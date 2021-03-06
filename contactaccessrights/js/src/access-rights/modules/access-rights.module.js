/* eslint-env amd */

define([
  'common/angular',
  'access-rights/modules/access-rights.apis',
  'access-rights/modules/access-rights.config',
  'access-rights/modules/access-rights.controllers',
  'access-rights/modules/access-rights.core',
  'access-rights/modules/access-rights.models',
  'access-rights/modules/access-rights.run'
], function (angular) {
  'use strict';

  angular.module('access-rights', [
    'access-rights.core',
    'access-rights.config',
    'access-rights.run',
    'access-rights.apis',
    'access-rights.controllers',
    'access-rights.models'
  ]);

  return angular;
});
