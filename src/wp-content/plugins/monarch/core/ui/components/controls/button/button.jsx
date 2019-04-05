// External dependencies
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import noop from 'lodash/noop';
import Ripple from 'react-ink';

// Internal dependencies
import './button.scss';


class ETCoreButton extends PureComponent {

  static defaultProps = {
    type:               'button',
    tagName:            'button',
    block:              false,
    elevate:            false,
    ink:                true,
    inverse:            false,
    large:              false,
    primary:            false,
    success:            false,
    small:              false,
    disabled:           false,
    disableClick:       false,
    additionalAttrs:    {},
    onMouseDown:        noop,
    onMouseUp:          noop,
    onMouseEnter:       noop,
    onMouseLeave:       noop,
    componentDidMount:  noop,
    componentDidUpdate: noop,
    _onClick:           noop,
    stopPropagation:    false,
  };

  static propTypes = {
    block:              PropTypes.bool,
    children:           PropTypes.node,
    tagName:            PropTypes.string,
    elevate:            PropTypes.bool,
    danger:             PropTypes.bool,
    ink:                PropTypes.bool,
    inverse:            PropTypes.bool,
    large:              PropTypes.bool,
    primary:            PropTypes.bool,
    round:              PropTypes.bool,
    success:            PropTypes.bool,
    info:               PropTypes.bool,
    alt:                PropTypes.bool,
    warning:            PropTypes.bool,
    small:              PropTypes.bool,
    disabled:           PropTypes.bool,
    disableClick:       PropTypes.bool,
    additionalAttrs:    PropTypes.object,
    type:               PropTypes.string,
    onMouseDown:        PropTypes.func,
    onMouseUp:          PropTypes.func,
    _onClick:           PropTypes.func,
    componentDidMount:  PropTypes.func,
    componentDidUpdate: PropTypes.func,
    className:          PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.object,
    ]),
    style:              PropTypes.object,
  };

  defaultStyle = {
    width:  '200%',
    height: '200%',
    top:    '-50%',
    left:   '-50%',
  };

  componentDidMount() {
    this.props.componentDidMount(this);
  }

  componentDidUpdate() {
    this.props.componentDidUpdate(this);
  }

  _onClick = (e) => {
    e.preventDefault();

    if (this.props.stopPropagation) {
      e.stopPropagation();
    }

    this.props._onClick(this);
  };

  _renderRipple = () => {
    return (
      <Ripple radius={150} duration={1200} background={false} options={{background: false}} style={this.defaultStyle}/>
    );
  };

  render() {
    let classes = classNames({
      'et-fb-button':              true,
      'et-fb-button--block':       this.props.block,
      'et-fb-button--danger':      this.props.danger,
      'et-fb-button--danger-alt':  this.props.danger && this.props.alt,
      'et-fb-button--elevate':     this.props.elevate,
      'et-fb-button--globalitem':  this.props.globalitem,
      'et-fb-button--info':        this.props.info,
      'et-fb-button--info-alt':    this.props.info && this.props.alt,
      'et-fb-button--inverse':     this.props.inverse,
      'et-fb-button--large':       this.props.large,
      'et-fb-button--primary':     this.props.primary,
      'et-fb-button--primary-alt': this.props.primary && this.props.alt,
      'et-fb-button--round':       this.props.round,
      'et-fb-button--small':       this.props.small,
      'et-fb-button--success':     this.props.success,
      'et-fb-button--active':      this.props.activeButton,
      'et-fb-button--warning':     this.props.warning,
      'et-fb-button--tooltip':     this.props.isTooltipButton,
    }, this.props.className);

    if ('button' === this.props.tagName) {
      return (
        <button
          type={this.props.type}
          data-tip={this.props.tooltip}
          className={classes}
          style={this.props.style}
          onClick={this._onClick}
          onMouseDown={this.props.onMouseDown}
          onMouseUp={this.props.onMouseUp}
          onMouseEnter={this.props.onMouseEnter}
          onMouseLeave={this.props.onMouseLeave}
          disabled={this.props.disabled}
        >
          {this.props.children}
          {this.props.ink ? this._renderRipple() : null}
        </button>
      );
    } else {
      return React.createElement(
        this.props.tagName,
        {
          className:   classes,
          'data-tip':  this.props.tooltip,
          style:       this.props.style,
          onClick:     this._onClick,
          onMouseDown: this.props.onMouseDown,
          onMouseUp:   this.props.onMouseUp,
        },
        this.props.children,
        (this.props.ink ? this._renderRipple() : null)
      );
    }
  }
}


export default ETCoreButton;
