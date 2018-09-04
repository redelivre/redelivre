// External Dependencies
import forEach from 'lodash/forEach';
import get from 'lodash/get';
import isUndefined from 'lodash/isUndefined';
import includes from 'lodash/includes';
import keys from 'lodash/keys';
import union from 'lodash/union';
import isArray from 'lodash/isArray';
import isString from 'lodash/isString';
import isNumber from 'lodash/isNumber';
import toString from 'lodash/toString';


function _getFieldDependencies(field, type = 'show') {
  let deps = [];

  if (! isUndefined(field[`${type}_if`])) {
    deps = keys(field[`${type}_if`]);
  }

  if (! isUndefined(field[`${type}_if_not`])) {
    deps = union(deps, keys(field[`${type}_if_not`]));
  }

  return deps;
}

function _decide(dependency_value, _if, _if_not) {
  let decision = false;

  if (_if && _if_not) {
    if (isArray(_if)) {
      decision = includes(_if, dependency_value) && ! includes(_if_not, dependency_value);
    } else {
      decision = _if === dependency_value && dependency_value !== _if_not;
    }

  } else if (_if) {
    decision = isArray(_if) ? includes(_if, dependency_value) : _if === dependency_value;

  } else if (_if_not) {
    decision = isArray(_if_not) ? ! includes(_if_not, dependency_value) : _if_not !== dependency_value;
  }

  return decision;
}


export function canShowField(field, property_resolver) {
  const show = [];

  forEach(_getFieldDependencies(field), dependency => {
    let show_if     = get(field, ['show_if', dependency]);
    let show_if_not = get(field, ['show_if_not', dependency]);

    const dependency_value = property_resolver.resolve(dependency);
    
    // Convert show_if/show_if_not to strings if dependancy value is a string for correct comparison.
    // Sometimes show_if defined as number and check will fail when compared to string
    if (isString(dependency_value)) {
      show_if     = ! isUndefined(show_if) && isNumber(show_if) ? toString(show_if) : show_if;
      show_if_not = ! isUndefined(show_if_not) && isNumber(show_if_not) ? toString(show_if_not) : show_if_not;
    }

    show.push(_decide(dependency_value, show_if, show_if_not));
  });

  return ! includes(show, false);
}


export function isReadOnlyField(field, property_resolver) {
  const readonly = [];

  forEach(_getFieldDependencies(field, 'readonly'), dependency => {
    const readonly_if     = get(field, ['readonly_if', dependency]);
    const readonly_if_not = get(field, ['readonly_if_not', dependency]);

    const dependency_value = property_resolver.resolve(dependency);

    readonly.push(_decide(dependency_value, readonly_if, readonly_if_not));
  });

  return ! includes(readonly, false);
}
