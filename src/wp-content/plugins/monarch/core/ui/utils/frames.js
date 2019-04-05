// External Dependencies
import {
  defaults,
  isEmpty,
  get,
  includes,
  forEach,
  some,
} from 'lodash';
import $ from 'jquery';

// Internal Dependencies


let documentWrites = []
window._et_document_write = content => documentWrites.push(content);

const maybeFixInlineScript = (element) => {
  if ('SCRIPT' !== element.nodeName) {
    return element;
  }

  let textContent = get(element, 'textContent', '');

  if (isEmpty(textContent)) {
    return element;
  }

  if (includes(textContent, 'document.write')) {
    // If the script uses document.write, use our wrapper
    element.textContent = textContent = textContent.split('document.write').join('window.top._et_document_write');
  }

  if (includes(textContent, 'jQuery')) {
    // If the script uses jQuery, make sure it's defined
    element.textContent = `window.jQuery = window.jQuery || window.top && window.top.jQuery;${textContent}`;
  }

  return element;
}

const IS_YARN_START = 'development' === process.env.NODE_ENV && ! process.env.DEV_SERVER;


class ETCoreFrames {

  /**
   * Instances of this class.
   *
   * @since ??
   *
   * @type {Object.<string, ETCoreFrames>}
   */
  static _instances = {};

  /**
   * jQuery object for the base window.
   *
   * @since ??
   *
   * @type {function(string, string=): jQuery}
   */
  $base;

  /**
   * jQuery object for the target window.
   *
   * @since ??
   *
   * @type {function(string, string=): jQuery}
   */
  $target;

  /**
   * Frames that are currently in use.
   *
   * @since ??
   *
   * @type {Object.<string, jQuery>}
   */
  active_frames = {};

  /**
   * Regex instances that match scripts that should not be put into frames.
   *
   * @since ??
   *
   * @type {RegExp}
   */
  exclude_scripts = /document\.location *=|apex\.live|(hotjar|googletagmanager|maps\.googleapis)\.com/i;

  /**
   * Cached frames available for use.
   *
   * @since ??
   *
   * @type {jQuery[]}
   */
  frames = [];

  /**
   * ETCoreFrames constructor
   *
   * @since ??
   *
   * @param {string}  [base_window]   Path to get Window from which to get styles and scripts.
   * @param {string}  [target_window] Path to get Window into which the frame should be inserted.
   */
  constructor(base_window = 'self', target_window = 'self') {
    this.base_window   = get(window, base_window);
    this.target_window = get(window, target_window);
    this.$base         = this.base_window.jQuery;
    this.$target       = this.target_window.jQuery;
  }

  _copyResourcesToFrame = $iframe => {
    const $html           = this.$base('html');
    const $body           = $html.find('body');
    const $resources      = $body.find('style, link');
    const $head_resources = $html.find('head').find('style, link');
    const $scripts        = $body.find('_script');

    const iframe_window = this.getFrameWindow($iframe);

    defaults(iframe_window, this.base_window);

    const $iframe_body = $iframe.contents().find('body');

    $iframe_body.parent().addClass('et-core-frame__html');

    $head_resources.each(function() {
      $iframe_body.prev().append(jQuery(this).clone());
    });

    $resources.each(function() {
      $iframe_body.append(jQuery(this).clone());
    });

    $scripts.each(function() {
      const script = iframe_window.document.createElement('script');

      script.src = jQuery(this).attr('src');

      iframe_window.document.body.appendChild(script);
    });
  };

  _createElement = (base_element, target_document) => {
    const resources = [];

    this._filterElementContent(base_element);

    // Handle resources nested below the top level
    $(base_element).find('link', 'script', 'style').each((i, node) => {
      resources.push(this._createResourceElement(node, target_document));
      node.parentNode.remove(node);
    });

    const element = target_document.importNode(base_element, true);

    $(element).find('iframe').remove();

    forEach(resources, resource => element.appendChild(resource));

    return element;
  };

  _createFrame = (id, move_dom = false, parent = 'body') => {
    const is_chrome = /chrome/i.test(window.navigator.userAgent);
    const is_safari = /safari/i.test(window.navigator.userAgent);
    const $iframe   = this.$target('<iframe>', {
      src: `javascript:'<!DOCTYPE html><html><body></body></html>'`,
    });

    let load_count = 0;

    $iframe.on('load', () => {
      if (0 === load_count++ && (is_chrome || is_safari)) {
        // Chrome & Safari fire two load events, we want the second one.
        return;
      }

      if (move_dom) {
        this._moveDOMToFrame($iframe);
      } else {
        this._copyResourcesToFrame($iframe);
      }
    });

    $iframe
      .addClass('et-core-frame')
      .attr('id', id)
      .appendTo(this.$target(parent))
      .parents()
      .addClass('et-fb-root-ancestor');

    // Add .et-fb-iframe-ancestor classname from app frame until body
    $iframe
      .parentsUntil('body')
      .addClass('et-fb-iframe-ancestor');

    return $iframe;
  };

  _createResourceElement = (base_element, target_document) => {
    const { id, nodeName: name, href, src, rel } = base_element;

    const attrs = ['id', 'src', 'href', 'type', 'rel', 'innerHTML', 'media', 'screen', 'crossorigin'];

    if ('et-fb-top-window-css' === id) {
      return; // continue
    }

    if ('et-frontend-builder-css' === id && IS_YARN_START) {
      return; // continue
    }

    if ('SCRIPT' === name && this._isScriptExcluded(base_element)) {
      return; // continue
    }

    const element = target_document.createElement(name);

    if ((src || href) && ('LINK' !== name || 'stylesheet' === rel)) {
      this.loading.push(this._resourceLoadAsPromise(element));
    }

    if ('SCRIPT' === name) {
      element.async = element.defer = false;
    }

    forEach(attrs, attr => base_element[attr] ? element[attr] = base_element[attr] : '');

    return element;
  };

  _isScriptExcluded = node => {
    return this.exclude_scripts.test(node.innerHTML) || (node.src && this.exclude_scripts.test(node.src));
  };

  _maybeCreateFrame = () => {
    if (isEmpty(this.frames)) {
      requestAnimationFrame(() => {
        this.frames.push(this._createFrame());
      });
    }
  };

  _filterElementContent = (node) => {
    if (node.id === 'page-container') {
      const $mobileMenu = $(node).find('#mobile_menu');
      if ($mobileMenu.length > 0) {
        $mobileMenu.remove();
      }
    }
  };

  _moveDOMToFrame = $iframe => {
    const base_head       = this.base_window.document.head;
    const body_children   = this.$base('body').contents().not('iframe, #wpadminbar').get();

    const target_window   = this.getFrameWindow($iframe);
    const target_document = $iframe.contents()[0];
    const target_head     = $iframe.contents()[0].head;
    const target_body     = $iframe.contents()[0].body;

    const resource_nodes  = ['LINK', 'SCRIPT', 'STYLE'];
    const loading         = [];

    documentWrites = [];

    this.loading = [];

    forEach(base_head.childNodes, child => {
      const is_resource = includes(resource_nodes, child.nodeName);

      let element;

      if (is_resource) {
        element = this._createResourceElement(child, target_document)

        if (! element) {
          return; // continue
        }

      } else {
        element = this._createElement(child, target_document);
      }

      target_head.appendChild(maybeFixInlineScript(element));
    });

    target_body.className = this.base_window.ET_Builder.Misc.original_body_class;

    forEach(body_children, child => {
      const is_resource = includes(resource_nodes, child.nodeName);

      let element = is_resource
        ? this._createResourceElement(child, target_document)
        : this._createElement(child, target_document);

      if (! element) {
        return; // continue
      }

      target_body.appendChild(maybeFixInlineScript(element));
    });

    if (documentWrites.length > 0) {
      jQuery(target_body).append(documentWrites.join(';'));
    }

    Promise.all(this.loading).then(() => {
      // Fire events again since browser fired before we added content to the frame
      const frame_document = $iframe[0].contentDocument;
      const frame_window   = $iframe[0].contentWindow;

      let dom_content_event;
      let load_event;

      if ('function' !== typeof(Event)) {
        dom_content_event = document.createEvent('Event');
        load_event        = document.createEvent('Event');

        dom_content_event.initEvent('DOMContentLoaded', true, true);
        load_event.initEvent('load', true, true);

      } else {
        dom_content_event = new Event('DOMContentLoaded');
        load_event        = new Event('load');
      }
      
      // Add small delay before firiing the events to give some Extra time to attach event handlers
      // Otherwise it may fire to early and event handlers attachment will fail.
      setTimeout(() => {
        frame_document.dispatchEvent(dom_content_event);
        frame_window.dispatchEvent(load_event);
      }, 0);
    }).catch(err => console.error(err));
  };

  _resourceLoadAsPromise(resource) {
    return new Promise((resolve) => {
      resource.addEventListener('load', resolve);
      resource.addEventListener('error', resolve);
    });
  }

  /**
   * Gets a frame if it exists, creates a new one otherwise.
   *
   * @since ??
   *
   * @param {Object}  options                    Options
   * @param {string}  options.id                 Unique identifier for the frame.
   * @param {Object}  [options.classnames]       CSS classes
   * @param {string}  [options.classnames.frame] CSS classes for the frame.
   * @param {string}  [options.classnames.body]  CSS classes for the frame's body element.
   * @param {boolean} [options.move_dom]         Whether or not to move the entire DOM from base window to the frame.
   * @param {string}  [options.parent]           CSS selector for the frame's parent element.
   *
   * @return {jQuery}
   */
  get({ id = '', classnames = { frame: '', body: '' }, move_dom = false, parent = 'body' }) {
    if (this.active_frames[id]) {
      return this.active_frames[id];
    }

    if (move_dom) {
      this.active_frames[id] = this._createFrame(id, move_dom, parent);
    } else {
      this.active_frames[id] = this.frames.pop() || this._createFrame(id, move_dom, parent);
    }

    const iframe_window = this.getFrameWindow(this.active_frames[id]);

    iframe_window.name = id;

    return this.active_frames[id];
  }

  /**
   * Gets an iframe's {@see window} object;
   *
   * @param {jQuery} $iframe
   *
   * @return {Window}
   */
  getFrameWindow($iframe) {
    return $iframe[0].contentWindow || $iframe[0].contentDocument;
  }

  static instance(id, base_window = 'self', target_window = 'self') {
    if (! ETCoreFrames._instances[id]) {
      ETCoreFrames._instances[id] = new ETCoreFrames(base_window, target_window);
    }

    return ETCoreFrames._instances[id];
  }

  release(id) {
    setTimeout(() => {
      const $frame = this.get({ id });

      if (! $frame) {
        return;
      }

      $frame[0].className = 'et-core-frame';

      $frame.removeAttr('id');
      $frame.removeAttr('style');

      this.frames.push($frame);

      delete this.active_frames[id];
    }, 250);
  }
}


export default ETCoreFrames;
