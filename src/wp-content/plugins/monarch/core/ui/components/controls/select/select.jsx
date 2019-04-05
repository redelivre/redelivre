// External Dependencies
import React, { PureComponent } from 'react';
import propTypes from 'prop-types';
import classnames from 'classnames';
import map from 'lodash/map';
import toString from 'lodash/toString';
import isUndefined from 'lodash/isUndefined';
import isArray from 'lodash/isArray';
import isEmpty from 'lodash/isEmpty';
import isObject from 'lodash/isObject';
import includes from 'lodash/includes';
import keys from 'lodash/keys';
import get from 'lodash/get';
import concat from 'lodash/concat';
import difference from 'lodash/difference';
import intersection from 'lodash/intersection';

// Internal Dependencies
import ETCoreControlSelectOption from '../select-option/select-option';
import ETCoreControlSelectOptgroup from '../select-optgroup/select-optgroup';

import './select.scss';


class ETCoreControlSelect extends PureComponent {

  static propTypes = {
    name: propTypes.string.isRequired,
  }

  componentDidMount() {
    if (this.props.group_prop) {
      this.props._onChange(this.props.group_prop, this.getGroupForSelected());
    }
  }

  getGroupForSelected = () => jQuery(this.node).find(':selected').parent().attr('label');

  _onChange = event => {
    const attrName = this.props.name;
    const group    = this.getGroupForSelected();
    const overwriteOnchange = this.props.overwrite_onchange;
    const valueOverwrite = this.props.value_overwrite;

    let selectedValue = event.target.value;

    if (group) {
      selectedValue = `${group}|${selectedValue}`;

      if (this.props.group_prop) {
        this.props._onChange(this.props.group_prop, group);
      }
    }

    this.props._onChange(attrName, selectedValue);

    // Updating other fields based on select's selected option, if the overwrite_onchange and value_overwrite attributes are set
    if (overwriteOnchange && isArray(overwriteOnchange) && valueOverwrite && isObject(valueOverwrite)) {
      map(this.props.overwrite_onchange, attr => {
        if (! isUndefined(valueOverwrite[selectedValue])) {
          this.props._onChange(attr, valueOverwrite[selectedValue]);
        }
      });
    }
  };

  _render_options(options, group = '') {

    let list = keys(options);
    const props = this.props;
    if (!isEmpty(props.order)) {
      // option keys in `list` are all strings, ensure the same happens in `order`
      const order = map(props.order, String);
      // keys that are in both arrays go first, `order` dictates the order
      // then we add any other option key that might me missing in `order`
      list = concat(intersection(order, list), difference(list, order))
    }

    return map(list, key => {
      const option = get(options, key);
      let _key = key;

      if ('' !== group) {
        _key = `${group}-${key}`;
      }

      return (
        <ETCoreControlSelectOption key={_key} value={key} name={option}/>
      );
    });
  }

  render() {
    let value = toString(this.props.value) ? this.props.value : this.props.default;
    let optionNodes;

    if (! includes(keys(this.props.options), value) && includes(keys(this.props.options), toString(value))) {
      value = toString(value);
    }

    // Classname
    const className = {
      'et-core-control-select': true,
      'et-fb-settings-option-select': true,
    };

    if (this.props.className) {
      className[this.props.className] = true;
    }

    if (this.props.groups) {
      optionNodes = map(this.props.options, (options, group) => {
        return '0' === group
          ? this._render_options(options, group)
          : (
            <ETCoreControlSelectOptgroup label={group} key={`option-group-${group}`}>
              {this._render_options(options, group)}
            </ETCoreControlSelectOptgroup>
          );
      });

    } else {
      optionNodes = this._render_options(this.props.options);
    }

    const additional_attrs = {};

    if (this.props.readonly) {
      additional_attrs.disabled = true;
    }

    let { id } = this.props;

    if (! id) {
      id = `et-fb-${this.props.name}`;
    }

    return (
      <select
        ref={node => this.node = node}
        className={classnames(className)}
        value={value}
        name={this.props.name}
        id={id}
        onChange={this._onChange}
        {...additional_attrs}>
        {optionNodes}
      </select>
    );
  }
}

export default ETCoreControlSelect;
