define([
    'common/mocks/module'
], function (mocks) {
    'use strict';

    mocks.factory('api.appraisal-cycle.mock', ['$q', function ($q) {
        return {
            all: jasmine.createSpy('all').and.callFake(function (filters, pagination, value) {
                var list, start, end;

                list = value || this.mockedCycles().list;

                if (filters) {
                    list = list.filter(function (cycle) {
                        return Object.keys(filters).every(function (key) {
                            return cycle[key] === filters[key];
                        });
                    });
                }

                if (pagination) {
                    start = pagination.page * pagination.size;
                    end = start + pagination.size;

                    list = list.slice(start, end);
                }

                return promiseResolvedWith({
                    list: list,
                    total: list.length,
                    allIds: list.map(function (cycle) {
                        return cycle.id;
                    }).join(',')
                })
            }),
            create: jasmine.createSpy('create').and.callFake(function (attributes, value) {
                var created = value || (function () {
                    var created = angular.copy(attributes);

                    created.id = '' + Math.ceil(Math.random() * 5000);
                    created.createdAt = Date.now();

                    return created;
                })();

                return promiseResolvedWith(created);
            }),
            find: jasmine.createSpy('find').and.callFake(function (id, value) {
                var cycle = value || this.mockedCycles().list.filter(function (cycle) {
                    return cycle.id === id;
                })[0];

                return promiseResolvedWith(cycle);
            }),
            grades: jasmine.createSpy('grades').and.callFake(function (value) {
                var defaults = [
                    { label: '1', value: 30 },
                    { label: '2', value: 10 },
                    { label: '3', value: 55 },
                    { label: '4', value: 87 },
                    { label: '5', value: 54 }
                ];

                return promiseResolvedWith(value || defaults);
            }),
            statuses: jasmine.createSpy('statuses').and.callFake(function (value) {
                var defaults = [
                    { id: '1', label: 'status 1', value: '1', weight: '1' },
                    { id: '2', label: 'status 2', value: '2', weight: '2' }
                ];

                return promiseResolvedWith(value || defaults);
            }),
            statusOverview: jasmine.createSpy('statusOverview').and.callFake(function (params) {
                return promiseResolvedWith([
                    {
                        status_id: 1,
                        status_name: "Awaiting self appraisal",
                        contacts_count: { due: 4, overdue: 2 }
                    },
                    {
                        status_id: 2,
                        status_name: "Awaiting manager appraisal",
                        contacts_count: { due: 10, overdue: 6 }
                    },
                    {
                        status_id: 3,
                        status_name: "Awaiting grade",
                        contacts_count: { due: 20, overdue: 12 }
                    },
                    {
                        status_id: 4,
                        status_name: "Awaiting HR approval",
                        contacts_count: { due: 7, overdue: 3 }
                    },
                    {
                        status_id: 5,
                        status_name: "Complete",
                        contacts_count: { due: 13, overdue: 8 }
                    }
                ]);
            }),
            update: jasmine.createSpy('update').and.callFake(function (id, attributes, value) {
                var cycle = value || (function () {
                    var cycle = this.mockedCycles().list.filter(function (cycle) {
                        return cycle.id === id;
                    })[0];

                    return AppraisalCycleInstanceMock(angular.extend({}, cycle, attributes));
                }.bind(this))();

                return promiseResolvedWith(cycle);
            }),
            total: jasmine.createSpy('total').and.callFake(function (filters, value) {
                var list = this.mockedCycles().list;

                if (filters) {
                    list = list.filter(function (cycle) {
                        return Object.keys(filters).every(function (key) {
                            return cycle[key] === filters[key];
                        });
                    });
                }

                return promiseResolvedWith(list.length);
            }),
            types: jasmine.createSpy('types').and.callFake(function (value) {
                var defaults = [
                    { id: '1', label: 'type 1', value: '1', weight: '1' },
                    { id: '2', label: 'type 2', value: '2', weight: '2' },
                    { id: '3', label: 'type 3', value: '3', weight: '3' }
                ];

                return promiseResolvedWith(value || defaults);
            }),

            /**
             * Mocked cycles
             */
            mockedCycles: function () {
                return {
                    total: 10,
                    list: [
                        {
                            id: '42131',
                            cycle_name: 'Appraisal Cycle 1',
                            cycle_is_active: true,
                            cycle_type_id: '2',
                            cycle_start_date: '01/01/2014',
                            cycle_end_date: '01/01/2015',
                            cycle_self_appraisal_due: '01/01/2016',
                            cycle_manager_appraisal_due: '02/01/2016',
                            cycle_grade_due: '03/01/2016'
                        },
                        {
                            id: '42132',
                            cycle_name: 'Appraisal Cycle 2',
                            cycle_is_active: true,
                            cycle_type_id: '1',
                            cycle_start_date: '02/02/2014',
                            cycle_end_date: '02/02/2015',
                            cycle_self_appraisal_due: '02/02/2016',
                            cycle_manager_appraisal_due: '04/02/2016',
                            cycle_grade_due: '05/02/2016'
                        },
                        {
                            id: '42133',
                            cycle_name: 'Appraisal Cycle 3',
                            cycle_is_active: true,
                            cycle_type_id: '2',
                            cycle_start_date: '03/03/2014',
                            cycle_end_date: '03/03/2015',
                            cycle_self_appraisal_due: '06/03/2016',
                            cycle_manager_appraisal_due: '07/03/2016',
                            cycle_grade_due: '08/03/2016'
                        },
                        {
                            id: '42134',
                            cycle_name: 'Appraisal Cycle 4',
                            cycle_is_active: true,
                            cycle_type_id: '3',
                            cycle_start_date: '04/04/2014',
                            cycle_end_date: '04/04/2015',
                            cycle_self_appraisal_due: '09/04/2016',
                            cycle_manager_appraisal_due: '10/04/2016',
                            cycle_grade_due: '11/04/2016'
                        },
                        {
                            id: '42135',
                            cycle_name: 'Appraisal Cycle 5',
                            cycle_is_active: true,
                            cycle_type_id: '3',
                            cycle_start_date: '05/05/2014',
                            cycle_end_date: '05/05/2015',
                            cycle_self_appraisal_due: '12/05/2016',
                            cycle_manager_appraisal_due: '13/05/2016',
                            cycle_grade_due: '14/05/2016'
                        },
                        {
                            id: '42136',
                            cycle_name: 'Appraisal Cycle 6',
                            cycle_is_active: false,
                            cycle_type_id: '1',
                            cycle_start_date: '06/06/2014',
                            cycle_end_date: '06/06/2015',
                            cycle_self_appraisal_due: '15/06/2016',
                            cycle_manager_appraisal_due: '16/06/2016',
                            cycle_grade_due: '17/06/2016'
                        },
                        {
                            id: '4217',
                            cycle_name: 'Appraisal Cycle 7',
                            cycle_is_active: false,
                            cycle_type_id: '2',
                            cycle_start_date: '07/07/2014',
                            cycle_end_date: '07/07/2015',
                            cycle_self_appraisal_due: '18/07/2016',
                            cycle_manager_appraisal_due: '19/07/2016',
                            cycle_grade_due: '20/07/2016'
                        },
                        {
                            id: '42138',
                            cycle_name: 'Appraisal Cycle 8',
                            cycle_is_active: true,
                            cycle_type_id: '1',
                            cycle_start_date: '08/08/2014',
                            cycle_end_date: '08/08/2015',
                            cycle_self_appraisal_due: '21/08/2016',
                            cycle_manager_appraisal_due: '22/08/2016',
                            cycle_grade_due: '23/08/2016'
                        },
                        {
                            id: '42139',
                            cycle_name: 'Appraisal Cycle 9',
                            cycle_is_active: true,
                            cycle_type_id: '1',
                            cycle_start_date: '09/09/2014',
                            cycle_end_date: '09/09/2015',
                            cycle_self_appraisal_due: '24/09/2016',
                            cycle_manager_appraisal_due: '25/09/2016',
                            cycle_grade_due: '26/09/2016'
                        },
                        {
                            id: '421310',
                            cycle_name: 'Appraisal Cycle 10',
                            cycle_is_active: true,
                            cycle_type_id: '4',
                            cycle_start_date: '10/10/2014',
                            cycle_end_date: '10/10/2015',
                            cycle_self_appraisal_due: '27/10/2016',
                            cycle_manager_appraisal_due: '28/10/2016',
                            cycle_grade_due: '29/10/2016'
                        }
                    ]
                }
            }
        };

        /**
         * Returns a promise that will resolve with the given value
         *
         * @param {any} value
         * @return {Promise}
         */
        function promiseResolvedWith(value) {
            var deferred = $q.defer();
            deferred.resolve(value);

            return deferred.promise;
        }
    }]);
})
