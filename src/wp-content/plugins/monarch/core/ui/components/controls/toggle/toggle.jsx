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
    const { name, value, _onChange, readonly, defaultValue } = this.props;
    const toggleFrom = !value && defaultValue ? defaultValue : value;
    const newValue                             = toggleFrom === 'on' ? 'off' : 'on';

    if (! readonly) {
      _onChange(name, newValue);
    }
  };

  render() {
    let { className, onClick, value, name, id, defaultValue } = this.props;

    let isEqual = ! isUndefined(this.props.button_options) && 'equal' === this.props.button_options.button_type;
    const visualValue = !value && defaultValue ? defaultValue : value;

    let classes = classnames({
      'et-core-control-toggle':        true,
      'et-core-control-toggle--equal': isEqual,
      'et-core-control-toggle--on':    visualValue === 'on',
      'et-core-control-toggle--off':   ! visualValue || visualValue === 'off',
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
        <div className="et-core-control-toggle__label et-core-control-toggle__label--on">
          <div className="et-core-control-toggle__text">{this.props.options.on}</div>
          <div className="et-core-control-toggle__handle"/>
        </div>
        <div className="et-core-control-toggle__label et-core-control-toggle__label--off">
          <div className="et-core-control-toggle__text">{this.props.options.off}</div>
          <div className="et-core-control-toggle__handle"/>
        </div>
        <input type="hidden" id={id} name={name} value={value}/>
      </div>
    );
  }
}


export default ETCoreControlToggle;
