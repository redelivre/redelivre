// External Dependencies
import map from 'lodash/map';
import has from 'lodash/has';
import get from 'lodash/get';
import isUndefined from 'lodash/isUndefined';
import size from 'lodash/size';
import includes from 'lodash/includes';
import dropRight from 'lodash/dropRight';


class PropertyResolver {
  /**
   * Prepended to all paths before performing property value lookups in source object.
   * For example, if the source object was 'module', the prefix was 'props.attrs.', and the
   * path being looked up was 'provider', it would be looked up using path: 'module.props.attrs.provider'.
   *
   * @since 3.10
   *
   * @type {string}
   */
  path_prefix = '';

  /**
   * Regex Patterns
   *
   * @since 3.10
   *
   * @type {{scope: RegExp, variable: RegExp}}
   */
  patterns = {
    scope:    /^(\w+):(.+)/,                 // Matches scope:variable
    variable: /(\w*)\${(\w*?):?(\w+)}(\w*)/, // Matches ${variable} or ${scope:variable}
    function: /^(function)\.(.+)/,           // Matches function.functionName
  };

  /**
   * Used to lookup default values for properties.
   *
   * @since 3.10
   *
   * @type {object}
   */
  property_definitions = {};

  /**
   * The object used to lookup property values.
   *
   * @since 3.10
   *
   * @type {object}
   */
  source_object = {};

  /**
   * PropertyResolver constructor.
   *
   * @since 3.10
   *
   * @param {object} source_object
   * @param {object} property_definitions
   * @param {string} path_prefix
   */
  constructor(source_object, property_definitions, path_prefix = '') {
    this.property_definitions = property_definitions || {};
    this.source_object        = source_object || {};
    this.path_prefix          = path_prefix;
  }

  /**
   * Resolves variables in a path string to values of properties in the source object.
   *
   * @since 3.10
   * @private
   *
   * @param {string} path
   *
   * @return {string}
   */
  _resolveVariables(path) {
    return map(path.split('.'), property => {
      if (this.matchesPattern(property, 'variable')) {
        // This property includes a variable that resolves to the value of another property.
        // For example, let's say the property is '${provider}_list'. The matches would be...
        let [_, before, scope, prop, after] = property.match(this.patterns.variable);
        // '${provider}_list', '', 'provider', '_list'

        let source = this.source_object;

        if (scope && has(source, scope)) {
          // Resolve the variable from an object within the source object. For example,
          // if the property was '${parentModule:provider}_list', the source object would be
          // 'this.source_object.parentModule'
          source = get(source, scope);
        }

        // Continuing from above example, if the value of 'provider' property on the source object was 'mailchimp' the
        // resolved property would be 'mailchimp_list'.
        prop = get(source, `${this.path_prefix}${prop}`);

        if (isUndefined(prop)) {
          prop = get(this.property_definitions, [prop, 'default']);
        }

        property = before + prop + after;
      }

      return property;

    }).join('.');
  }

  /**
   * Whether or not a string matches a regex pattern located in {@see this.patterns}.
   *
   * @since 3.10
   *
   * @param {string} string  The string to test.
   * @param {string} pattern The name of the pattern to test against.
   *
   * @return {boolean}
   */
  matchesPattern = (string, pattern) => this.patterns[pattern].test(string);

  /**
   * Whether or not a Provider/List has predefined Custom Fields
   *
   * @since 3.10
   *
   * @return {string}
   */
  hasPredefinedFields = () => {
    const attrs            = get(this, 'source_object.props.attrs');
    const provider         = get(attrs, 'provider');
    const allow_dynamic    = get(this, 'property_definitions.use_custom_fields.allow_dynamic');

    if (provider) {
      let [account, list] = get(attrs, `${provider}_list`, '').split('|');

      if (account) {
        account = account.toLowerCase().replace(/ /g, '');

        let key               = `predefined_field_${provider}_${account}`;
        let predefined_fields = get(this, ['child_property_definitions', key, 'options']);

        if (list && ! predefined_fields) {
          key               = `${key}_${list}`;
          predefined_fields = get(this, ['child_property_definitions', key, 'options'], []);
        }

        return (
          // Provider allows custom fields even if not predefined
          includes(allow_dynamic, provider) ||
          // At least one custom field is predefined other than the default 'none'
          size(predefined_fields) > 1
        ) ? 'on' : 'off';
      }
    }

    return 'off';
  };

  /**
   * Resolves a property value using the provided path.
   *
   * @since 3.10
   *
   * @param {string} path
   *
   * @return {*}
   */
  resolve(path) {
    let source = this.source_object;
    let scope;
    let _;

    if (this.matchesPattern(path, 'function')) {
      [_, scope, path] = path.match(this.patterns.function);
      return has(this, path) ? get(this, path)() : false;
    } else if (this.matchesPattern(path, 'scope')) {
      [_, scope, path] = path.match(this.patterns.scope);
    }

    if (scope && has(source, scope)) {
      // Resolve the path using an object within the source object. For example,
      // if the path was 'parentModule:mailchimp_list', the source object would be
      // 'this.source_object.parentModule'
      source = get(source, scope);
    }

    // Resolve any variables in the path
    path = this._resolveVariables(path);

    // Use path to get the value from the source
    let value = get(source, `${this.path_prefix}${path}`);

    if (isUndefined(value)) {
      value = get(this.property_definitions, [path, 'default']);
    }

    return value;
  }
}


export default PropertyResolver;
