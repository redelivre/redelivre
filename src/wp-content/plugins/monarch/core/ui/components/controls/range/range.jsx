// External Dependencies
import React, { PureComponent } from 'react';
import propTypes from 'prop-types';
import classnames from 'classnames';
import isUndefined from 'lodash/isUndefined';
import debounce from 'lodash/debounce';
import toString from 'lodash/toString';
import get from 'lodash/get';

// Internal Dependencies
import './range.scss';


class ETCoreRange extends PureComponent {

  static defaultProps = {
    default_unit: '',
  };

  static propTypes = {
    name:         propTypes.string.isRequired,
    default_unit: propTypes.string,
    precision:    propTypes.number,
  };

  isChangingRange = false;

  constructor(props) {
    super(props);

    const rangeSettings = get(this, 'props.range_settings', { min : 0, max : 100, step : 1 });
    const value         = toString(this.props.value);

    this.state = {
      rangeMin: rangeSettings.min,
      rangeMax: rangeSettings.max,
      rangeStep: rangeSettings.step,
      value: this.emptyIfDefault(value),
    };
  }

  componentDidMount() {
    let value = this.props.value;

    // Create a debounced update function to validate the input after the user is done typing
    this.deferredUpdate = debounce(this.updateOptionAndState, 700);

    // set the step to 0.1 if we need to update step and value is not integer
    if (parseFloat(this.state.rangeStep) > 0.1 && (parseFloat(value) % 1 > 0)) {
      this.setState({
        rangeStep: '0.1',
      });
    }

    this.checkRangeBoundaries(this.state.value);
  }

  componentDidUpdate( prevProps ) {
    const value = this.emptyIfDefault(this.props.value);

    // update value in state when value changed (via props) by another component
    if (prevProps.value !== value && ! this.userUpdate) {
      this.setState({ value });
    }

    this.userUpdate = false;
  }

  componentWillUnmount() {
    // Cancel any deferred update to prevent setState errors
    this.deferredUpdate.cancel();
  }

  getDefaultValue() {
    return toString(this.props.default);
  }

  emptyIfDefault(value) {
    // Return an empty string if value is equal to default, value otherwise
    return value === this.getDefaultValue() ? '' : value;
  }

  updateOptionAndState = value => {
    // If value is not defined, use the state
    value = isUndefined(value) ? this.state.value : value;

    this.userUpdate = true;

    this.setState({ value: this.emptyIfDefault(value) });
    this.props._onChange(this.props.name, value);
  }

  _updateFromRange = event => {
    this.isChangingRange = true;
    this.updateOptionAndState(get(event, 'target.value'));
    this.isChangingRange = false;
  }

  _onChange = event => {
    const value = get(event, 'target.value');

    this.updateOptionAndState(value);
    this.checkRangeBoundaries(value);
  }

  // check the range slider boundaries against the provided value and extend min or max boundary if needed
  checkRangeBoundaries(raw_slider_value) {
    // no need to adjust boundaries if slider has no value
    if (! toString(raw_slider_value)) {
      return;
    }

    const slider_value = parseFloat(raw_slider_value);

    // extend max boundary of the slider if needed
    if (slider_value > this.state.rangeMax) {
      this.setState({
        rangeMax: slider_value,
      });
    }

    // extend min boundary of the slider if needed
    if (slider_value < this.state.rangeMin) {
      this.setState({
        rangeMin: slider_value,
      });
    }

    // set the step to 0.1 if we need to update step and value is not integer
    if (parseFloat(this.state.rangeStep) > 0.1 && (slider_value % 1 > 0)) {
      this.setState({
        rangeStep: '0.1',
      });
    }
  }

  _renderInput() {
    const id   = this.props.id || `et-fb-${this.props.name}`;
    const name = this.props.name;

    // Get value from state
    const value = this.emptyIfDefault(this.state.value);

    // Get default value based on current responsive tab
    const defaultValue = this.getDefaultValue();

    // Get range value. Range value needs to reflect current default placeholder/value
    const rangeValue = value === '' ? defaultValue : value;

    return (
      <div className='et-fb-settings-option-inputs-wrap'>
        <input
          id={id}
          name={name}
          type='range'
          min={this.state.rangeMin}
          max={this.state.rangeMax}
          step={this.state.rangeStep}
          className='et-fb-range'
          value={parseFloat(rangeValue)}
          data-shortcuts-allowed
          onChange={this._updateFromRange}
        />
        <div className='et-fb-range-number et-fb-settings-option-input'>
          <input
            type='number'
            value={value}
            placeholder={defaultValue}
            onChange={this._onChange}
          />
        </div>
      </div>
    );
  }

  render() {
    const defaultValue         = this.getDefaultValue();
    const value                = this.props.value;

    let buttonReset    = '';
    let innerClassName = {
      'et-fb-settings-option-inner': true,
      'et-fb-settings-option-inner-range': true,
    };

    if ( value && value !== defaultValue ) {
      buttonReset = <button className='et-fb-settings-option-button--reset' onClick={this.reset} />;
    }

    return (
      <div className={classnames( innerClassName )}>
        {this._renderInput()}
        {buttonReset}
      </div>
    );
  }
}


export default ETCoreRange;
