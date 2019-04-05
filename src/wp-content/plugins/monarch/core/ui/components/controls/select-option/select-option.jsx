// External Dependencies
import React, { PureComponent } from 'react';
import { AllHtmlEntities } from 'html-entities';

const HtmlEntities = new AllHtmlEntities();

class ETCoreControlSelectOption extends PureComponent {

  render() {
    return (
      <option value={this.props.value}>{HtmlEntities.decode(this.props.name)}</option>
    );
  }
}

export default ETCoreControlSelectOption;
