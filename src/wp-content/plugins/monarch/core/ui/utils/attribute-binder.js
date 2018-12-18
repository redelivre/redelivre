// External Dependencies
import $ from 'jquery';
import {
  forEach,
  pickBy,
  debounce,
  isFunction,
} from 'lodash';


let _instance = null;


class AttributeBinder {

  _attrs     = new Set;
  _callbacks = new Set;
  _config    = {
    attributes:    true,
    characterData: true,
    childList:     true,
    subtree:       true,
    attributeFilter: ['style', 'height', 'class']
  };

  _elements = new Map;
  _observer;

  constructor() {
    if ( _instance) {
      return _instance;
    }

    this._observer = new MutationObserver(this.onMutation);
  }

  bind = (src, target, attrs = []) => {
    let call_observe = false;

    if (! this._elements.has(src)) {
      call_observe = true;

      this._elements.set(src, new Map);
    }

    if (! this._elements.get(src).has(target)) {
      this._elements.get(src).set(target, new Set);
    }

    if (isFunction(attrs)) {
      this._callbacks.add(attrs);
    } else {
      forEach(attrs, attr => this._elements.get(src).get(target).add(attr));
      forEach(attrs, attr => this._attrs.add(attr));
    }

    if (call_observe) {
      this._observer.observe(src, this._config);
    }
  };

  static instance() {
    if (! _instance) {
      _instance = new AttributeBinder;
    }

    return _instance;
  }

  onMutation = debounce(mutations => {
    if (this._callbacks.size > 0) {
      this._callbacks.forEach(callback => callback());
      return;
    }

    forEach(mutations, mutation => {
      if (! this._attrs.has(mutation.attributeName)) {
        return; // continue
      }

      if (! this._elements.has(mutation.target)) {
        return; // continue
      }

      const { attributeName, attributeValue } = mutation;

      let targets = this._elements.get(mutation.target);

      targets = pickBy(targets, attrs => attrs.has(mutation.attributeName));

      forEach(keys(targets), target => $(target).css(attributeName, attributeValue));
    });
  }, 250, {
    leading: true,
    maxWait: 250
  }).bind(this);
}


export default AttributeBinder;
