import { DragDropContext } from 'react-dnd';
import { Component } from 'react';
import PropTypes from 'prop-types';
import { DragDropManager } from 'dnd-core';
import HTML5Backend from 'react-dnd-html5-backend';

/**
 * Singleton for getting HTML5 Backend context to ensure it is only initialized once.
 *
 * @see https://github.com/react-dnd/react-dnd/issues/186
 */
let _dndContext;

function getDndContext() {
  if(_dndContext) {
    return _dndContext;
  }

  const topWindow = window.top || window;

  _dndContext = new DragDropManager(HTML5Backend, {window: topWindow});

  return _dndContext;
}

/**
 * DnD Context Wrapper which uses context singleton to ensure no double HTML5 Backend
 * This wrapper also automatically switch window context for BFB
 */
class ETDragDropContextWrapper extends Component {
  static childContextTypes = {
    dragDropManager: PropTypes.object.isRequired
  };

  getChildContext() {
    return {
      dragDropManager: getDndContext(),
    }
  }

  render() {
    return(
      <div>
        {this.props.children}
      </div>
    )
  }
}

export {
  ETDragDropContextWrapper,
}

export default DragDropContext(HTML5Backend);
