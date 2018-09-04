import ETCoreButton from './button/button';
import ETCoreButtonGroup from './button-group/button-group';
import ETCoreInput from './input/input';
import ETCoreSelect from './select/select';
import ETCoreSelectOption from './select-option/select-option';
import ETCoreSelectOptgroup from './select-optgroup/select-optgroup';
import ETCoreSortableList from './sortable-list/sortable-list';
import ETCoreToggle from './toggle/toggle';
import ETCoreRange from './range/range';

const controlTypeMap = {
  button:        ETCoreButton,
  text:          ETCoreInput,
  select:        ETCoreSelect,
  sortable_list: ETCoreSortableList,
  toggle:        ETCoreToggle,
  yes_no_button: ETCoreToggle,
  range:         ETCoreRange,
};

export {
  ETCoreButton,
  ETCoreButtonGroup,
  ETCoreInput,
  ETCoreSelect,
  ETCoreSelectOption,
  ETCoreSelectOptgroup,
  ETCoreSortableList,
  ETCoreToggle,
  ETCoreRange,
};

export function getControl(type) {
  return controlTypeMap[type];
}
