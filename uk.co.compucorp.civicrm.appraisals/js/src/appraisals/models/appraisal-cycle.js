define([
    'common/lodash',
    'common/moment',
    'appraisals/modules/models',
    'common/models/model',
    'common/services/api/appraisal-cycle'
], function (_, moment, models) {
    'use strict';

    models.factory('AppraisalCycle', ['Model', 'api.appraisal-cycle', 'AppraisalCycleInstance', function (Model, appraisalCycleAPI, instance) {

        return Model.extend({

            /**
             * Returns the active cycles
             *
             * @return {Promise}
             */
            active: function () {
                return appraisalCycleAPI.total({ cycle_is_active: true });
            },

            /**
             * Returns a list of appraisal cycles, each converted to a model instance
             *
             * @param {object} filters - Values the full list should be filtered by
             * @param {object} pagination
             *   `page` for the current page, `size` for number of items per page
             * @return {Promise}
             */
            all: function (filters, pagination) {
                return appraisalCycleAPI.all(this.processFilters(filters), pagination).then(function (response) {
                    response.list = response.list.map(function (cycle) {
                        return instance.init(cycle, true);
                    });

                    return response;
                });
            },

            /**
             * Creates a new appraisal cycle
             *
             * @param {object} attributes - The attributes of the cycle to be created
             * @return {Promise} - Resolves with the new cycle
             */
            create: function (attributes) {
                var cycle = instance.init(attributes).toAPI();

                return appraisalCycleAPI.create(cycle).then(function (newCycle) {
                    return instance.init(newCycle, true);
                });
            },

            /**
             * Finds an appraisal cycle by id
             *
             * @param {string} id
             * @return {Promise} - Resolves with the new cycle
             */
            find: function (id) {
                return appraisalCycleAPI.find(id).then(function (cycle) {
                    return instance.init(cycle, true);
                });
            },

            /**
             * Returns the grades data
             *
             * @return {Promise}
             */
            grades: function () {
                return appraisalCycleAPI.grades();
            },

            /**
             * Returns the list of all possible appraisal cycle statuses
             *
             * @return {Promise}
             */
            statuses: function () {
                return appraisalCycleAPI.statuses().then(function (statuses) {
                    return statuses.map(function (status) {
                        return _.pick(status, ['value', 'label']);
                    });
                });
            },

            /**
             * Returns the full appraisal cycles status overview
             *
             * The overview is an object containing the individual steps overview
             * and the total of all the appraisals in the cycles
             *
             * @param {object} params
             *   `current_date`: the date used to check if an appraisal is overdue
             *     or not (defaults to current date if not passed)
             *   `start_date`  : limits the status overview from this date on
             *   `end_date`    : limits the status overview up to this date
             *   `cycles_ids`  : comma-separated string of ids of the cycles the
             *     overview must be limited on
             * @return {Promise} resolves to an object structured like this:
             *   {
             *       steps: [
             *           { '21': { name: 'Step 1', due: 20, overdue: 2 } },
             *           ...
             *           { '42': { name: 'Step 5', due: 2, overdue: 33 } },
             *       ],
             *       totalAppraisalNumber: 78
             *   }
             */
            statusOverview: function (params) {
                return appraisalCycleAPI.statusOverview(_.defaults(params || {}, {
                        current_date: moment().format('YYYY-MM-DD')
                    }))
                    .then(function (status) {
                        return {
                            steps: _.reduce(status, function (accumulator, step) {
                                accumulator[step.status_id] = {
                                    name: step.status_name,
                                    due: step.contacts_count.due,
                                    overdue: step.contacts_count.overdue
                                };

                                return accumulator;
                            }, {}),
                            totalAppraisalsNumber: _.reduce(status, function (accumulator, step) {
                                return accumulator + step.contacts_count.due + step.contacts_count.overdue;
                            }, 0)
                        };
                    });
            },

            /**
             * Returns the list of all possible appraisal cycle types
             *
             * @return {Promise}
             */
            total: function () {
                return appraisalCycleAPI.total();
            },

            /**
             * Returns the list of all possible appraisal cycle types
             *
             * @return {Promise}
             */
            types: function () {
                return appraisalCycleAPI.types().then(function (types) {
                    return types.map(function (types) {
                        return _.pick(types, ['value', 'label']);
                    });
                });
            },
        });
    }]);
});
