// External Dependencies
import toString from 'lodash/toString';
import isEqual from 'lodash/isEqual';


const Utils = {

  decodeHtmlEntities(value) {
    value = toString(value);

    return value.replace(/&#(\d+);/g, (match, dec) => String.fromCharCode(dec));
  },

  shouldComponentUpdate(component, nextProps, nextState) {
    return ! isEqual(nextProps, component.props) || ! isEqual(nextState, component.state);
  },

};

const {
  decodeHtmlEntities,
  shouldComponentUpdate,
} = Utils;

export {
  decodeHtmlEntities,
  shouldComponentUpdate,
}

export default Utils;
