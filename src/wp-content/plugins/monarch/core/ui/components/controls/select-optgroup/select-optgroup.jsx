// External Dependencies
import React, { PureComponent } from 'react';


class ETCoreControlSelectOptgroup extends PureComponent {

  render() {
    return (
      <optgroup label={this.props.label}>
        {this.props.children}
      </optgroup>
    );
  }
}

export default ETCoreControlSelectOptgroup;
