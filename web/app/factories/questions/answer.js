'use strict';

angular.module('formBuilder')
.factory('QuestionsAnswer', [
  'QuestionsDependency',
  function(QuestionsDependency) {
  return function() {
    var properties = {
      name: '',
      type: '',
      size: null,
      position: '',
      formula: '',
      compute: '',
      dataType: '',
      display: {
        hasAttributes: false,
        hasValidation: false,
        hasLabels: false,
        hasDependency: false,
        enabled: true,
      },
      attributes: [],
      validation: {},
      labelId: '',
      labels: {},
      dependency: {
        question: '',
        answer: '',
        list: []
      },
      countId: '',
      value: '',
      id: null,
    };

    var questionsAnswer = {
      properties: properties,
      getProperties: function() {
        return properties;
      },
      addDependency: function(data) {
        properties.dependency.list.push({
          source: null,
          answer: null,
        });
      },
      addAttribute: function(data) {
        if (!data) {
          data = {key: '', value: ''};
        }

        properties.attributes.push(data);
      },
      getCountId: function() {
        return properties.countId;
      },
      setCountId: function(count) {
        properties.countId = count;
      },
      getType: function() {
        return properties.type;
      },
      getDataType: function() {
        return properties.dataType;
      },
      hasDependency: function() {
        return properties.display.hasDependency;
      },
      getDependencyList: function() {
        return properties.dependency.list;
      },
      getOptionList: function() {
        return properties.option;
      },
      setName: function(name) {
        properties.name = name;
      },
      setType: function(type) {
        properties.type = type;
      },
      setDataType: function(dataType) {
        properties.dataType = dataType;
      },
      setSize: function(size) {
        properties.size = size;
      },
      setPosition: function(position) {
        properties.position = position;
      },
      setFormula: function(formula) {
        properties.formula = formula;
      },
      setDisplay: function(display) {
        properties.display = display;
      },
      setAttributes: function(attributes) {
        properties.attributes = attributes;
      },
      setValidation: function(validation) {
        properties.validation = validation;
      },
      setLabelId: function(labelId) {
        properties.labelId = labelId;
      },
      setLabels: function(labels) {
        properties.labels = labels;
      },
      setValue: function(value) {
        properties.value = value;
      },
      setId: function(id) {
        properties.id = id;
      },
      setOption: function(option) {
        properties.option = option;
      },
      setDependency: function(dependency) {
        properties.dependency.question = dependency.question;
        properties.dependency.answer = dependency.answer;

        angular.forEach(dependency.list, function(value, key) {
          var dep = {
            source: value.source,
            answer: value.answer,
          };

          if (angular.isDefined(value.option)) {
            dep.option = value.option;
          }

          properties.dependency.list.push(dep);
        });
      },
      setCompute: function(compute) {
        properties.compute = compute;
      },
      setProperties: function(property) {
        questionsAnswer.setName(property.name);
        questionsAnswer.setDataType(property.dataType);
        questionsAnswer.setType(property.type);
        questionsAnswer.setSize(property.size);
        questionsAnswer.setPosition(property.position);
        questionsAnswer.setFormula(property.formula);
        questionsAnswer.setDisplay(property.display);
        questionsAnswer.setAttributes(property.attributes);
        questionsAnswer.setValidation(property.validation);
        questionsAnswer.setLabelId(property.labelId);
        questionsAnswer.setLabels(property.labels);
        questionsAnswer.setValue(property.value);
        questionsAnswer.setId(property.id);

        if (angular.isDefined(property.compute)) {
          questionsAnswer.setCompute(property.compute);
        }

        if (angular.isDefined(property.dependency)) {
          questionsAnswer.setDependency(property.dependency);
        }

        if (angular.isDefined(property.option)) {
          questionsAnswer.setOption(property.option);
        }
      }
    };

    return questionsAnswer;
  };
}]);
