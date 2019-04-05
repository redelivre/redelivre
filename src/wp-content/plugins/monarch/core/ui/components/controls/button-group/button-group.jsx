// External dependencies
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

// Internal dependencies
import './button-group.scss';


/**
 * <ETBuilderButtonGroup />
 */
class ETCoreButtonGroup extends PureComponent {

  static propTypes = {
    alt:       PropTypes.bool,
    block:     PropTypes.bool,
    children:  PropTypes.node,
    className: PropTypes.string,
    danger:    PropTypes.bool,
    elevate:   PropTypes.bool,
    info:      PropTypes.bool,
    inverse:   PropTypes.bool,
    onClick:   PropTypes.func,
    primary:   PropTypes.bool,
    style:     PropTypes.object,
    success:   PropTypes.bool,
    vertical:  PropTypes.bool,
    warning:   PropTypes.bool,
  };

  render() {
    const {
      alt,
      block,
      children,
      className,
      danger,
      elevate,
      info,
      inverse,
      onClick,
      primary,
      style,
      success,
      vertical,
      warning,
    } = this.props;

    let classes = classNames({
      'et-fb-button-group': true,
      'et-fb-button-group--block': block,
      'et-fb-button-group--danger': danger,
      'et-fb-button-group--elevate': elevate,
      'et-fb-button-group--info': info,
      'et-fb-button-group--inverse': inverse,
      'et-fb-button-group--primary': primary,
      'et-fb-button-group--primary-alt': primary && alt,
      'et-fb-button-group--success': success,
      'et-fb-button-group--vertical': vertical,
      'et-fb-button-group--warning': warning,
    }, className);

    return (
      <div
        className={classes}
        style={style}
        onClick={onClick}
      >
        {children}
      </div>
    );
  }
}


export default ETCoreButtonGroup;
