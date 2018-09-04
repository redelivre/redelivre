// External dependencies
import React, { PureComponent } from 'react';
import classnames from 'classnames';
import noop from 'lodash/noop';
import isUndefined from 'lodash/isUndefined';

// Internal dependencies
import './toggle.scss';


class ETCoreControlToggle extends PureComponent {

  static defaultProps = {
    value:     'off',
    _onChange: noop,
  };

  _onChange = () => {
    const { name, value, _onChange, readonly } = this.props;
    const newValue                             = value === 'on' ? 'off' : 'on';

    if (! readonly) {
      _onChange(name, newValue);
    }
  };

  render() {
    let { className, onClick, value, name, id } = this.props;

    let isEqual = ! isUndefined(this.props.button_options) && 'equal' === this.props.button_options.button_type;

    let classes = classnames({
      'et-core-control-toggle':        true,
      'et-core-control-toggle--equal': isEqual,
      'et-core-control-toggle--on':    value === 'on',
      'et-core-control-toggle--off':   ! value || value === 'off',
    }, className);

    if ( ! id ) {
      id = `et-fb-${name}`;
    }

    let additional_attrs = {};

    if (this.props.readonly) {
      additional_attrs.disabled = true;
    }

    return (
      <div className={classes} onClick={onClick || this._onChange} {...additional_attrs}>
        <div className="et-core-control-toggle__label et-core-control-toggle__label--on">{this.props.options.on}</div>
        <div className="et-core-control-toggle__label et-core-control-toggle__label--off">{this.props.options.off}</div>
        <div className="et-core-control-toggle__handle"/>
        <input type="hidden" id={id} name={name} value={value} />
      </div>
    );
  }
}


export default ETCoreControlToggle;
