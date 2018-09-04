// External Dependencies
import React, { PureComponent } from 'react';
import classnames from 'classnames';
import { Motion, spring } from 'react-motion';
import map from 'lodash/map';
import forEach from 'lodash/forEach';
import assign from 'lodash/assign';
import isUndefined from 'lodash/isUndefined';
import isNumber from 'lodash/isNumber';
import isEmpty from 'lodash/isEmpty';
import $ from 'jquery';
import set from 'lodash/set';
import get from 'lodash/get';
import has from 'lodash/has';
import noop from 'lodash/noop';
import defaults from 'lodash/defaults';
import clone from 'lodash/clone';
import ReactTooltip from 'react-tooltip';

// Internal Dependencies
import { ETCoreButtonGroup, ETCoreButton, ETCoreInput } from '..';
import ETCoreIcon from '../../icon/icon';
import ETCoreSortableListItem from './sortable-list-item';
import withDragDropContext from '../../hoc/drag-drop-context';
import Constants from '../../../constants/controls';

import './sortable-list.scss';


class ETCoreSortableList extends PureComponent {

  static defaultProps = {
    onEditingLink:        noop,
    _onAddItem:           noop,
    animation:            true,
    linkSettingsShowIcon: true,
  };

  nextDragID = -1;

  constructor(props) {
    super(props);

    let items = JSON.parse(this.props.value || '[]');

    if (0 === items.length && ! this.props.allowEmpty) {
      const field_type = get(this.props, 'module.props.module.props.attrs.field_type', false);
      const checked    = get(this.props, 'module.props.module.props.attrs.checkbox_checked', 'off');

      let value = '';

      if ('checkbox' === field_type) {
        value = get(this.props, 'module.props.module.props.attrs.field_title', '');
      }

      items = [
        {
          value,
          checked: 'on' === checked ? 1 : 0,
          dragID:  this._nextDragID(),
        }
      ];

      this.focusItemInput();

    } else {
      forEach(items, item => item.dragID = this._nextDragID());
    }

    this.state = {
      items:        JSON.stringify(items),
      editing_link: false,
    };

    this.emitChange();
  }

  componentDidUpdate(prevProps) {
    const { value } = this.props;

    // update value in state when value changed (via props) by another component
    if (prevProps.value !== value && ! this.userUpdate) {
      this.setState({ items: value });
    }

    this.userUpdate = false;

    ReactTooltip.rebuild();
  }

  _nextDragID = () => this.nextDragID++;

  addItem(index, event, resetValue) {
    if (event) {
      event.preventDefault();
    }

    const value     = this.state.items || [];
    const items     = JSON.parse(value);
    const itemValue = items.length && false !== index && !resetValue ? items[index] : {};
    const dragID    = this._nextDragID();
    const checked   = 0;

    if (false !== index) {
      items.splice(index + 1, 0, { ...itemValue, dragID });
    } else {
      items.push({
        value:   '',
        checked,
        dragID,
      });
    }

    this.userUpdate = true;

    this.setState({ items: JSON.stringify(items) });

    this.emitChange();

    if (isNumber(index)) {
      this.focusItemInput(index + 1);
      this.props._onAddItem(index + 1);
    } else {
      this.focusItemInput();
      this.props._onAddItem(items.length - 1);
    }
  }

  emitChange = () => setTimeout(() => this.props._onChange(this.props.name, this.state.items), 0);

  focusItemInput = index => {
    setTimeout(() => {
      const itemInputs = this.itemsList.querySelectorAll('.et-core-control-sortable-list__row input');

      if (0 === itemInputs.length) {
        return;
      }

      if (false === index || isUndefined(index)) {
        index = itemInputs.length - 1;
      }

      const inputToFocus = itemInputs[index];

      inputToFocus.focus();
    });
  };

  moveItem = (sourceIndex, targetIndex) => {
    const items      = JSON.parse(this.state.items);
    const sourceItem = items[sourceIndex];

    items[sourceIndex] = items[targetIndex];
    items[targetIndex] = sourceItem;

    this.userUpdate = true;

    this.setState({ items: JSON.stringify(items) });
    this.emitChange();
  };

  editItem(index, event) {
    event.preventDefault();
    this.props._onAddItem(index);
  }

  onChangeLinkSettings = (setting, value) => {
    const items = JSON.parse(this.state.items);

    set(items, setting, value);

    this.userUpdate = true;

    this.setState({ items: JSON.stringify(items) });

    this.emitChange();
  };

  removeItem(index, event) {
    event.preventDefault();

    let value = this.state.items || '[]';
    let items = JSON.parse(value);

    items.splice(index, 1);

    if (0 === items.length && ! this.props.allowEmpty) {
      items = [
        {
          value:   '',
          checked: 0,
          dragID:  0,
        }
      ];
    }

    this.userUpdate = true;

    this.setState({ items: JSON.stringify(items) });

    this.emitChange();
  }

  showLinkSettings(index, event) {
    if (! event.target) {
      // Closing Modal
      index = false;

      if (Constants.SORTABLE_LIST_LINK_SETTINGS_CLOSE === event.props.buttonName) {
        // Discard Changes
        let value = this.state.items || '[]';
        let items = JSON.parse(value);

        items[this.state.editing_link] = this.link_settings_backup;

        this.setState({ items: JSON.stringify(items) });

        this.emitChange();
      }
    } else {
      // Opening Modal
      event.preventDefault();

      let value = this.state.items || '[]';
      let items = JSON.parse(value);

      this.link_settings_backup = clone(items[index]);

      defaults(this.link_settings_backup, {link_url: '', link_text: ''});
    }

    $('body').toggleClass('et-core-control-sortable-list-editing-link', false !== index);

    this.setState({ editing_link: index });

    this.props.onEditingLink(false !== index);
  }

  updateItems(index, item, isChecked, event) {
    event.preventDefault();

    let isClick    = 'click' === event.type;
    let value      = this.state.items || [];
    let items      = JSON.parse(value);
    let itemValue  = isClick ? item.value : event.target.value;
    let isCheckbox = ! isUndefined(this.props.checkbox) && true === this.props.checkbox;

    if (isEmpty(items)) {
      items.push(item);
    }

    if (isClick && ! isCheckbox) {
      forEach(items, item => item.checked = 0);
    }

    assign(items[index], {
      value:   itemValue,
      checked: isChecked ? 1 : 0,
    });

    this.userUpdate = true;

    this.setState({ items: JSON.stringify(items) });
    this.emitChange();
  }

  _renderAddNewItemButton() {
    const addNewItem   = this.addItem.bind(this, false, false);
    const defaultStyle = { size: 0, opacity: 0 };
    const style        = {
      size:    spring(1, { stiffness: 300, damping: 20 }),
      opacity: spring(1, { stiffness: 300, damping: 20 }),
    };

    return (
      <span className='et-fb-item-button-wrap--add'>
          <Motion defaultStyle={defaultStyle} style={style}>
            {interpolatingStyles => {
              const style = {
                opacity:   interpolatingStyles.opacity,
                transform: `scale( ${interpolatingStyles.size} )`,
              };

              return (
                <ETCoreButton round style={style} tooltip={this.props.tooltip} _onClick={addNewItem}>
                  <ETCoreIcon size="14" icon="add" color="#FFFFFF"/>
                </ETCoreButton>
              )
            }}
          </Motion>
          <label className="et-fb-form__label">{this.props.buttonLabel}</label>
        </span>
    );
  }

  _renderItems() {
    let value      = this.state.items || '[]';
    let items      = JSON.parse(value);
    let isRadio    = ! isUndefined(this.props.radio) && true === this.props.radio;
    let isCheckbox = ! isUndefined(this.props.checkbox) && true === this.props.checkbox;

    let right_actions = this.props.right_actions;

    if (this.props.readonly && has(this, 'props.right_actions_readonly')) {
      right_actions = this.props.right_actions_readonly;
    } else if (this.props.readonly && ! right_actions) {
      right_actions = 'move';
    } else if (! right_actions) {
      right_actions = 'move|copy|delete';
    }

    return map(items, (item, key) => {
      let isChecked = 1 === item.checked;

      let itemClasses = classnames({
        'et-core-control-sortable-list__row':           true,
        'et-core-control-sortable-list__row--radio':    isRadio,
        'et-core-control-sortable-list__row--checkbox': isCheckbox,
      });

      let checkClasses = classnames({
        'et-core-control-sortable-list__check':   true,
        'et-core-control-sortable-list--checked': isChecked,
      });

      const onCheck          = this.updateItems.bind(this, key, item, ! isChecked);
      const onChange         = this.updateItems.bind(this, key, item, isChecked);
      const addItem          = this.addItem.bind(this, key);
      const addNewItem       = this.addItem.bind(this, key, false, true);
      const removeItem       = this.removeItem.bind(this, key);
      const editItem         = this.editItem.bind(this, key);
      const showLinkSettings = this.showLinkSettings.bind(this, key);
      const value            = get(item, 'value', '');
      const fieldId          = get(item, 'field_id', '');
      const fieldTitle       = '' === get(item, 'field_title', '') ? fieldId : get(item, 'field_title', '');
      const processedValue   = '' === value ? fieldTitle : value;

      return (
        <ETCoreSortableListItem
          classes={itemClasses}
          key={item.dragID}
          id={item.dragID}
          index={key}
          moveItem={this.moveItem}
          isCheckbox={isCheckbox}
          isRadio={isRadio}
          checkClasses={checkClasses}
          onCheck={onCheck}
          onChange={onChange}
          onAdd={addNewItem}
          onCopy={addItem}
          onDelete={removeItem}
          onSetting={editItem}
          onLink={showLinkSettings}
          value={processedValue}
          useInput={this.props.useInput || ! this.props.readonly}
          readonly={this.props.readonly}
          left_actions={this.props.left_actions}
          right_actions={right_actions}
        />
      );
    });
  }

  _renderLinkSettings() {
    let value = this.state.items || '[]';
    let items = JSON.parse(value);

    if (! items[this.state.editing_link]) {
      return false;
    }

    const item    = items[this.state.editing_link];
    const onClick = this.showLinkSettings.bind(this, this.state.editing_link);

    const defaultStyle = {
      size:    .5,
      opacity: 0,
    };

    const style = {
      size:    this.props.animation ? spring(1, { stiffness: 300, damping: 20 }) : 1,
      opacity: this.props.animation ? spring(1, { stiffness: 300, damping: 20 }) : 1,
    };

    return (
      <Motion defaultStyle={defaultStyle} style={style}>
        {interpolatingStyles =>
          <div className="et-core-control-sortable-list__link-settings" style={{opacity: interpolatingStyles.opacity, transform: `scale( ${interpolatingStyles.size} )`}}>
            <h4>{this.props.labels.link_settings}</h4>
            <div>
              <p className="et-fb-form__label">{this.props.labels.link_url}</p>
              <ETCoreInput
                className="et-fb-settings-option-input et-fb-settings-option-input--block"
                type="text"
                name={`${this.state.editing_link}.link_url`}
                value={item.link_url}
                _onChange={this.onChangeLinkSettings}
              />
            </div>
            <div>
              <p className="et-fb-form__label">{this.props.labels.link_text}</p>
              <ETCoreInput
                className="et-fb-settings-option-input et-fb-settings-option-input--block"
                type="text"
                name={`${this.state.editing_link}.link_text`}
                value={item.link_text}
                _onChange={this.onChangeLinkSettings}
              />
            </div>
            <ETCoreButtonGroup block>
              <ETCoreButton
                block
                danger
                buttonName={Constants.SORTABLE_LIST_LINK_SETTINGS_CLOSE}
                tooltip={this.props.labels.link_cancel}
                _onClick={onClick}

              >
                {this.props.linkSettingsShowIcon ? <ETCoreIcon icon="exit" color="#FFFFFF"/> : null}
                {this.props.linkSettingsShowIcon ? null : this.props.labels.link_cancel}
              </ETCoreButton>
              <ETCoreButton
                block
                success
                buttonName={Constants.SORTABLE_LIST_LINK_SETTINGS_SAVE}
                tooltip={this.props.labels.link_save}
                _onClick={onClick}
              >
                {this.props.linkSettingsShowIcon ? <ETCoreIcon icon="check" color="#FFFFFF"/> : null}
                {this.props.linkSettingsShowIcon ? null : this.props.labels.link_save}
              </ETCoreButton>
            </ETCoreButtonGroup>
          </div>
        }
      </Motion>
    );
  }

  render() {
    let additional_attrs = this.props.additional_attrs ? this.props.additional_attrs : {};
    let id               = this.props.id;

    if (! id) {
      id = `et-fb-${this.props.name}`;
    }

    const editing_link = false !== this.state.editing_link;

    return (
      <div className="et-core-control-sortable-list" ref={list => this.itemsList = list}>
        {this._renderItems()}
        {editing_link && this._renderLinkSettings()}
        <textarea
          value={this.state.items}
          name={this.props.name}
          id={id}
          onChange={noop}
          {...additional_attrs}
        />
        {this.props.useAddNewButton && this._renderAddNewItemButton()}
      </div>
    );
  }
}

export default withDragDropContext(ETCoreSortableList);
