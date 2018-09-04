// External Dependencies
import React, { PureComponent } from 'react';
import ReactDOM from 'react-dom';
import classnames from 'classnames';
import PropTypes from 'prop-types';
import { DragSource, DropTarget } from 'react-dnd';
import map from 'lodash/map';
import capitalize from 'lodash/capitalize';
import compact from 'lodash/compact';

// Internal Dependencies
import ItemTypes from '../../../constants/dnd-types';
import ETCoreIcon from '../../icon/icon';


const moduleItemSource = {
  beginDrag(props) {
    return {
      id:    props.id,
      index: props.index,
    };
  },
};

const moduleItemTarget = {
  hover(props, monitor, component) {
    const dragIndex  = monitor.getItem().index;
    const hoverIndex = props.index;

    // Don't replace items with themselves
    if (dragIndex === hoverIndex) {
      return;
    }

    // Determine rectangle on screen
    const hoverBoundingRect = ReactDOM.findDOMNode(component).getBoundingClientRect();

    // Get vertical middle
    const hoverMiddleY = (hoverBoundingRect.bottom - hoverBoundingRect.top) / 2;

    // Determine mouse position
    const clientOffset = monitor.getClientOffset();

    // Get pixels to the top
    const hoverClientY = clientOffset.y - hoverBoundingRect.top;

    // Only perform the move when the mouse has crossed half of the items height
    // When dragging downwards, only move when the cursor is below 50%
    // When dragging upwards, only move when the cursor is above 50%

    // Dragging downwards
    if (dragIndex < hoverIndex && hoverClientY < hoverMiddleY) {
      return;
    }

    // Dragging upwards
    if (dragIndex > hoverIndex && hoverClientY > hoverMiddleY) {
      return;
    }

    // Time to actually perform the action
    props.moveItem(dragIndex, hoverIndex);

    // Note: we're mutating the monitor item here!
    // Generally it's better to avoid mutations,
    // but it's good here for the sake of performance
    // to avoid expensive index searches.
    monitor.getItem().index = hoverIndex;
  },
};


class ETCoreSortableListItem extends PureComponent {

  static defaultProps = {
    left_actions:  '',
    right_actions: 'move|copy|delete',
  };

  static propTypes = {
    id:                PropTypes.any.isRequired,
    classes:           PropTypes.string.isRequired,
    checkClasses:      PropTypes.string.isRequired,
    isDragging:        PropTypes.bool.isRequired,
    isCheckbox:        PropTypes.bool.isRequired,
    isRadio:           PropTypes.bool.isRequired,
    moveItem:          PropTypes.func.isRequired,
    onCheck:           PropTypes.func.isRequired,
    onChange:          PropTypes.func.isRequired,
    onAdd:             PropTypes.func.isRequired,
    onSettings:        PropTypes.func,
    onCopy:            PropTypes.func.isRequired,
    onDelete:          PropTypes.func.isRequired,
    connectDragSource: PropTypes.func.isRequired,
  };

  preventModalKeyPress = event => {
    if (this.props.useInput && 'Enter' === event.key) {
      event.preventDefault();
    }
  };

  handleOnKeyUp = event => {
    if (this.props.useInput && 'Enter' === event.key) {
      event.preventDefault();

      this.props.onAdd();
    }
  };

  _renderCheckbox = () => <a href="#" className={this.props.checkClasses} onClick={this.props.onCheck}/>;

  _renderInput() {
    const additional_props = {};

    if (this.props.readonly) {
      additional_props.readOnly = true;
    }

    return (
      <input
        type="text"
        value={this.props.value}
        onChange={this.props.onChange}
        onKeyPress={this.preventModalKeyPress}
        onKeyUp={this.handleOnKeyUp}
        {...additional_props}
      />
    );
  }

  _renderActions(actions) {
    return map(actions, action => {
      if ('link' === action && ! this.props.isCheckbox && ! this.props.isRadio) {
        return false;
      }

      const callback = `on${capitalize(action)}`;
      const icon     = 'link' === action ? 'text-link' : action;

      return (
        <a href="#" key={action} className={`et-core-control-sortable-list__${action}`} onClick={this.props[callback]}>
          <ETCoreIcon icon={icon} color="rgb(163, 176, 194)"/>
        </a>
      );
    });
  }

  _renderLeftSideActions() {
    const actions = compact(this.props.left_actions.split('|'));

    return (
      <div className="et-core-control-sortable-list__actions--left">
        {this._renderActions(actions)}
      </div>
    );
  }

  _renderRightSideActions() {
    const actions = compact(this.props.right_actions.split('|'));

    return (
      <div className="et-core-control-sortable-list__actions--right">
        {this._renderActions(actions)}
      </div>
    );
  }

  render() {
    const { isDragging, connectDragSource, connectDropTarget } = this.props;
    const style                                                = {
      opacity: this.props.isDragging ? 0 : 1,
    };

    let classes = classnames({
      'et-core-control-sortable-list__row--dragged':  isDragging,
      'et-core-control-sortable-list__row--no-input': ! this.props.useInput,
    }, this.props.classes);

    return connectDragSource(connectDropTarget(
      <div className={classes} style={style}>
        {(this.props.isRadio || this.props.isCheckbox) && this._renderCheckbox()}
        {this._renderLeftSideActions()}
        {this.props.useInput && this._renderInput()}
        {this.props.useInput || <span>{this.props.value}</span>}
        {this._renderRightSideActions()}
      </div>
    ));
  }
}

const DragSourceDecorator = DragSource(ItemTypes.MODULE_ITEM, moduleItemSource, function(connect, monitor) {
  return {
    connectDragSource: connect.dragSource(),
    isDragging:        monitor.isDragging(),
  };
});

const DropTargetDecorator = DropTarget(ItemTypes.MODULE_ITEM, moduleItemTarget, function(connect) {
  return {
    connectDropTarget: connect.dropTarget(),
  };
});

export default DropTargetDecorator(DragSourceDecorator(ETCoreSortableListItem));
