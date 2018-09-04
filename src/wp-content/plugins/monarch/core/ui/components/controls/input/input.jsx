// External Dependencies
import React, { PureComponent } from 'react';
import propTypes from 'prop-types';
import classnames from 'classnames';
import isUndefined from 'lodash/isUndefined';
import throttle from 'lodash/throttle';
import noop from 'lodash/noop';
import get from 'lodash/get';
import toString from 'lodash/toString';
import toNumber from 'lodash/toNumber';

// Internal Dependencies
import Utils from '../../../utils/utils';

import './input.scss';


class ETCoreControlInput extends PureComponent {

  static propTypes = {
    name: propTypes.string.isRequired,
  };

  emitChange = throttle(this._emitChange, 0, { 'leading': false });
  userUpdate = false;

  constructor(props) {
    super(props);

    const value        = toString(this.props.value);
    const defaultValue = toString(this.props.default);

    this.state = {
      value: value === defaultValue ? '' : value,
    };
  }

  componentDidUpdate(prevProps) {
    const { value } = this.props;

    // update value in state when value changed (via props) by another component
    if (prevProps.value !== value && ! this.userUpdate) {
      this.setState({ value });
    }

    this.userUpdate = false;
  }

  getDefaultValue() {
    return toString(this.props.default);
  }

  _onInput = event => {
    const value = event.target.value;

    // use state to store the value for the component. Otherwise cursor jumps to the end of the input while typing too fast.
    this.setState({ value });

    // emit change to parent component using throttled function to prevent lags on big pages.
    this.emitChange.cancel();
    this.emitChange();
  };

  _emitChange() {
    this.userUpdate = true;

    this.props._onChange(this.props.name, this.state.value);
  }

  _onBlur = event => {
    let value = Utils.decodeHtmlEntities(event.target.value);

    if (! isUndefined(this.props.valueMin) && toNumber(value) < this.props.valueMin) {
      value = this.props.valueMin;
    }

    if (! isUndefined(this.props.valueMax) && toNumber(value) > this.props.valueMax) {
      value = this.props.valueMax;
    }

    this.props._onChange(this.props.name, value);

    this.setState({value});
  };

  render() {
    const additional_attrs = get(this.props, 'additional_attrs', {});
    const class_name       = {
      'et-core-control-input': true,
    };

    if (this.props.className) {
      class_name[this.props.className] = true;
    }

    if (this.props.readonly) {
      additional_attrs.readOnly = true;
    }

    let { id, type } = this.props;

    if (! id) {
      id = `et-fb-${this.props.name}`;
    }

    if (! type) {
      type = 'text';
    }

    return (
      <input
        className={classnames(class_name)}
        type={type}
        value={this.state.value}
        name={this.props.name}
        id={id}
        onChange={noop}
        onInput={this._onInput}
        onBlur={this._onBlur}
        placeholder={this.getDefaultValue()}
        {...additional_attrs}
      />
    );
  }
}

export default ETCoreControlInput;
