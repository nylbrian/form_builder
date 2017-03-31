angular.module('formBuilder')
.filter('griddropdown', function () {
  return function (input, map, idField, valueField, initial) {
    if(typeof map !== "undefined") {
      for (var i = 0; i < map.length; i++) {
        if(map[i][idField] == input)
        {
          return map[i][valueField];
        }
      }
    } else if(initial) {
      return initial;
    }
    return input;
  };
});

angular.module('formBuilder')
.filter('gridanswer', function () {
  return function (input, row) {
    if (!input) {
      return;
    }

    if (angular.isDefined(row.entity.option)) {
      return row.entity.option.properties.name;
    } else {
      return input;
    }

    return 'Selected';
  };
});
