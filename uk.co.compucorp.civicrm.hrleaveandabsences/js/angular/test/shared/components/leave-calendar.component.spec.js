/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/moment',
    'common/lodash',
    'mocks/helpers/helper',
    'mocks/data/absence-period-data',
    'mocks/data/absence-type-data',
    'mocks/data/option-group-mock-data',
    'mocks/data/public-holiday-data',
    'mocks/data/work-pattern-data',
    'mocks/data/leave-request-data',
    'common/mocks/services/api/contact-mock',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/leave-request-api-mock',
    'mocks/apis/public-holiday-api-mock',
    'mocks/apis/option-group-api-mock',
    'mocks/apis/work-pattern-api-mock',
    'leave-absences/shared/config',
    'leave-absences/my-leave/app'
  ], function (angular, moment, _, helper, absencePeriodData, absenceTypeData, optionGroupMock, publicHolidayData, workPatternMocked, leaveRequestData) {
    'use strict';

    describe('leaveCalendar', function () {
      var $componentController, $controller, $controllerProvider, $log, $q,
        $rootScope, controller, $provide, AbsencePeriod, Calendar, LeaveRequest,
        OptionGroup, PublicHoliday, sharedSettings;
      var mockedCheckPermissions = mockCheckPermissionService();
      var currentContact = {
        id: CRM.vars.leaveAndAbsences.contactId,
        role: 'staff'
      };

      beforeEach(module('common.mocks', 'leave-absences.templates', 'leave-absences.mocks', 'my-leave', function (_$provide_, _$controllerProvider_) {
        $provide = _$provide_;
        $controllerProvider = _$controllerProvider_;
      }));

      beforeEach(inject(function (AbsencePeriodAPIMock, AbsenceTypeAPIMock, LeaveRequestAPIMock,
        PublicHolidayAPIMock, WorkPatternAPIMock) {
        $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
        $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
        $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
        $provide.value('PublicHolidayAPI', PublicHolidayAPIMock);
        $provide.value('WorkPatternAPI', WorkPatternAPIMock);
        $provide.value('checkPermissions', mockedCheckPermissions);
      }));

      beforeEach(inject(['api.contact.mock', function (ContactAPIMock) {
        $provide.value('api.contact', ContactAPIMock);
      }]));

      beforeEach(inject(function (_OptionGroup_, OptionGroupAPIMock) {
        OptionGroup = _OptionGroup_;

        spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
          return OptionGroupAPIMock.valuesOf(name);
        });
      }));

      beforeEach(inject([
        '$componentController', '$controller', '$log', '$q', '$rootScope', 'AbsencePeriod',
        'Calendar', 'LeaveRequest', 'PublicHoliday', 'shared-settings',
        function (_$componentController_, _$controller_, _$log_, _$q_, _$rootScope_,
          _AbsencePeriod_, _Calendar_, _LeaveRequest_, _PublicHoliday_, _sharedSettings_) {
          $componentController = _$componentController_;
          $controller = _$controller_;
          $log = _$log_;
          $q = _$q_;
          $rootScope = _$rootScope_;
          AbsencePeriod = _AbsencePeriod_;
          Calendar = _Calendar_;
          LeaveRequest = _LeaveRequest_;
          PublicHoliday = _PublicHoliday_;
          sharedSettings = _sharedSettings_;

          spyOn($log, 'debug');
          spyOn(AbsencePeriod, 'all');
          spyOn(Calendar, 'get').and.callThrough();
          spyOn(LeaveRequest, 'all').and.callThrough();
          spyOn(PublicHoliday, 'all').and.callThrough();

          // Set 2016 as current period, because Calendar loads data only for the current period initially,
          // and MockedData has 2016 dates
          amend2016Period({ current: true });

          compileComponent();
        }]));

      // The mocked "work pattern calendar" and "leave request" data is made for
      // the month of February, so we pretend we are in February
      beforeAll(function () {
        jasmine.clock().mockDate(new Date(2016, 1, 1));
      });

      afterAll(function () {
        jasmine.clock().uninstall();
      });

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('on init', function () {
        it('hides the loader for the whole page', function () {
          expect(controller.loading.page).toBe(false);
        });

        it('selects the current month', function () {
          expect(controller.selectedMonths).toEqual([moment().month()]);
        });

        it('loads the public holidays', function () {
          expect(PublicHoliday.all).toHaveBeenCalled();
        });

        it('loads the OptionValues of the leave request statuses and day types', function () {
          expect(OptionGroup.valuesOf).toHaveBeenCalledWith([
            'hrleaveandabsences_leave_request_status',
            'hrleaveandabsences_leave_request_day_type'
          ]);
        });

        describe('permissions', function () {
          it('checks if the user has permissions to manage L&A', function () {
            expect(mockedCheckPermissions).toHaveBeenCalledWith(sharedSettings.permissions.ssp.manage);
          });

          it('checks if the user has permissions to administer L&A', function () {
            expect(mockedCheckPermissions).toHaveBeenCalledWith(sharedSettings.permissions.admin.administer);
          });
        });

        describe('sub-controller', function () {
          describe('when the user is a staff', function () {
            it('injects the staff calendar sub-controller', function () {
              expect($log.debug).toHaveBeenCalledWith('LeaveCalendarStaffController');
            });
          });

          describe('when the user is a manager', function () {
            beforeEach(function () {
              currentContact.role = 'manager';
              compileComponent();
            });

            it('injects the manager calendar sub-controller', function () {
              expect($log.debug).toHaveBeenCalledWith('LeaveCalendarManagerController');
            });
          });

          describe('when the user is an admin', function () {
            beforeEach(function () {
              currentContact.role = 'admin';
              compileComponent();
            });

            it('injects the admin calendar sub-controller', function () {
              expect($log.debug).toHaveBeenCalledWith('LeaveCalendarAdminController');
            });
          });

          describe('when the role-override binding is used', function () {
            beforeEach(function () {
              currentContact.role = 'admin';
              compileComponent(false, { roleOverride: 'staff' });
            });

            it('ignores the real user\'s role and uses the one given in the binding instead', function () {
              expect($log.debug).not.toHaveBeenCalledWith('LeaveCalendarAdminController');
              expect($log.debug).toHaveBeenCalledWith('LeaveCalendarStaffController');
            });
          });

          afterEach(function () {
            currentContact.role = 'staff';
          });
        });

        describe('contacts', function () {
          var spyLoadContacts;

          beforeEach(function () {
            spyLoadContacts = spyOnSubCtrlLoadContacts();
            compileComponent();
          });

          it('gets the list of contacts from the currently injected sub-controller', function () {
            expect(spyLoadContacts).toHaveBeenCalled();
          });

          it('loads the contacts to display on the calendar', function () {
            expect(controller.contacts.length).not.toBe(0);
          });
        });

        describe('absence periods', function () {
          it('loads the absence periods', function () {
            expect(controller.absencePeriods.length).not.toBe(0);
          });

          it('sorts absence periods by start_date', function () {
            expect(controller.absencePeriods).toEqual(_.sortBy(absencePeriodData.all().values, 'start_date'));
          });

          it('selects the current period', function () {
            expect(controller.selectedPeriod.current).toBe(true);
          });

          it('creates the list of months of the selected period', function () {
            var months = controller.months;
            var periodStartDate = moment(controller.selectedPeriod.start_date);
            var periodEndDate = moment(controller.selectedPeriod.end_date);

            expect(months[0].index).toEqual(periodStartDate.month());
            expect(months[0].year).toEqual(periodStartDate.year());
            expect(months[months.length - 1].index).toEqual(periodEndDate.month());
            expect(months[months.length - 1].year).toEqual(periodEndDate.year());
          });
        });

        describe('absence types', function () {
          var AbsenceType;

          beforeEach(inject(function (_AbsenceType_) {
            AbsenceType = _AbsenceType_;
            spyOn(AbsenceType, 'all').and.callThrough();

            compileComponent();
          }));

          it('loads the absence types', function () {
            expect(controller.absenceTypes.length).not.toBe(0);
          });

          it('excludes the inactive absence types', function () {
            expect(AbsenceType.all).toHaveBeenCalledWith({
              is_active: true
            });
          });
        });

        describe('filter option values', function () {
          var optionGroups = ['hrjc_region', 'hrjc_location', 'hrjc_level_type', 'hrjc_department'];

          describe('when the filters should not be shown', function () {
            it('does not fetch the filters option values', function () {
              expect(OptionGroup.valuesOf).not.toHaveBeenCalledWith(optionGroups);
            });
          });

          describe('when the filters should be shown', function () {
            beforeEach(function () {
              compileComponent(true);
              controller.showFilters = true;
              $rootScope.$digest();
            });

            it('fetches the filters option values', function () {
              expect(OptionGroup.valuesOf).toHaveBeenCalledWith(optionGroups);
            });
          });
        });

        describe('contacts\' work pattern calendar', function () {
          it('loads the work pattern calendars', function () {
            expect(Calendar.get.calls.any()).toBe(true);
          });

          it('loads only the work pattern calendars of the currently loaded contacts', function () {
            expect(Calendar.get.calls.mostRecent().args[0]).toEqual(controller.contacts.map(function (contact) {
              return contact.id;
            }));
          });

          describe('splitting the loading by selected months', function () {
            it('loads the calendars individually for each selected month', function () {
              expect(Calendar.get.calls.count()).toBe(controller.selectedMonths.length);
            });

            it('uses the selected months\' first and last day as date delimiters', function () {
              Calendar.get.calls.all().forEach(function (call, index) {
                var callMonth = controller.months[controller.selectedMonths[index]];

                expect(call.args[1]).toBe(callMonth.days[0].date);
                expect(call.args[2]).toBe(callMonth.days[callMonth.days.length - 1].date);
              });
            });
          });
        });

        describe('leave requests', function () {
          it('loads the leave requests', function () {
            expect(LeaveRequest.all.calls.any()).toBe(true);
          });

          it('loads only the approved, admin approved, or awaiting approval leave requests', function () {
            expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
              status_id: {'IN': [
                optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'approved').value,
                optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'admin_approved').value,
                optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'awaiting_approval').value
              ]}
            }));
          });

          it('loads only the leave requests belonging to the loaded contacts', function () {
            expect(LeaveRequest.all.calls.mostRecent().args[0]).toEqual(jasmine.objectContaining({
              contact_id: { 'IN': [currentContact.id] }
            }));
          });

          describe('splitting the loading by selected months', function () {
            it('loads the leave requests individually for each selected month', function () {
              expect(LeaveRequest.all.calls.count()).toBe(controller.selectedMonths.length);
            });

            it('uses the selected months\' first and last day as date delimiters', function () {
              LeaveRequest.all.calls.all().forEach(function (call, index) {
                var callMonth = controller.months[controller.selectedMonths[index]];

                expect(call.args[0]).toEqual(jasmine.objectContaining({
                  from_date: { from: callMonth.days[0].date },
                  to_date: { to: callMonth.days[callMonth.days.length - 1].date }
                }));
              });
            });
          });

          describe('indexing', function () {
            var leaveRequests;

            beforeEach(function () {
              leaveRequests = controller.leaveRequests;
            });

            it('indexes the overall list of leave requests by contact id', function () {
              expect(Object.keys(leaveRequests)).toEqual(controller.contacts.map(function (contact) {
                return contact.id;
              }));
            });

            it('indexes the leave requests of a specific contact by date', function () {
              expect(Object.keys(leaveRequests[controller.contacts[0].id]).every(function (key) {
                return moment(key).isValid();
              })).toBe(true);
            });
          });
        });
      });

      describe('calendar data structure', function () {
        describe('month', function () {
          var july, daysInJuly;

          beforeEach(function () {
            july = controller.months[6];
            daysInJuly = moment().month(july.index).daysInMonth();
          });

          it('contains a unique identifier made of month+year indexes', function () {
            expect(july.id).toBe(july.index + '' + july.year);
          });

          it('contains a flag for the loading status', function () {
            expect(july.loading).toBeDefined();
          });

          it('contains a flag to mark if the contacts\' data had already been loaded', function () {
            expect(july.contactsDataLoaded).toBeDefined();
          });

          it('contains the month long name', function () {
            expect(july.name.long).toBe('July');
          });

          it('contains the month short name', function () {
            expect(july.name.short).toBe('Jul');
          });

          it('contains the month index', function () {
            expect(july.index).toBe(6);
          });

          it('contains the year', function () {
            expect(july.year).toBe(moment(controller.selectedPeriod.start_date).year());
          });

          it('contains the list of days', function () {
            expect(july.days.length).toEqual(daysInJuly);
          });

          describe('when the currently selected period does not start at the beginning of the month', function () {
            var january, daysInJanuary;

            beforeEach(function () {
              amend2016Period({ start_date: '2016-01-20' });
              compileComponent();
            });

            beforeEach(function () {
              january = controller.months[0];
              daysInJanuary = moment().month(january.index).daysInMonth();
            });

            it('still contains all the days anyway', function () {
              expect(january.days.length).toEqual(daysInJanuary);
            });
          });

          describe('when the currently selected period does not finish at the end of the month', function () {
            var december, daysInDecember;

            beforeEach(function () {
              amend2016Period({ end_date: '2016-12-26' });
              compileComponent();
            });

            beforeEach(function () {
              december = controller.months[11];
              daysInDecember = moment().month(december.index).daysInMonth();
            });

            it('still contains all the days anyway', function () {
              expect(december.days.length).toEqual(daysInDecember);
            });
          });
        });

        describe('day', function () {
          var twentiethOfJanuary;

          beforeEach(function () {
            twentiethOfJanuary = controller.months[0].days[19];
          });

          it('contains the date', function () {
            expect(twentiethOfJanuary.date).toBe('2016-01-20');
          });

          it('contains the day index', function () {
            expect(twentiethOfJanuary.index).toBe('20');
          });

          it('contains the name of day', function () {
            expect(twentiethOfJanuary.name).toBe('Wed');
          });

          it('contains the data specific for each contact in the calendar', function () {
            expect(twentiethOfJanuary.contactsData).toEqual(jasmine.any(Object));
          });

          describe('when the day is within the currently selected period', function () {
            it('is marked as enabled', function () {
              expect(twentiethOfJanuary.enabled).toBe(true);
            });
          });

          describe('when the day is outside the currently selected period', function () {
            beforeEach(function () {
              amend2016Period({ start_date: '2016-01-22' });
              compileComponent();

              twentiethOfJanuary = controller.months[0].days[19];
            });

            it('is marked as disabled', function () {
              expect(twentiethOfJanuary.enabled).toBe(false);
            });
          });
        });

        describe('day\'s data specific for each contact', function () {
          var contactData;

          it('is indexed by contact id', function () {
            var indexes = Object.keys(getDayWithType('working_day').contactsData);

            expect(indexes).toEqual(controller.contacts.map(function (contact) {
              return contact.id;
            }));
          });

          describe('when the day is a weekend for a contact', function () {
            beforeEach(function () {
              contactData = getDayWithType('weekend', true);
            });

            it('marks it as such', function () {
              expect(contactData.isWeekend).toBe(true);
            });
          });

          describe('when the day is a non-working day for a contact', function () {
            beforeEach(function () {
              contactData = getDayWithType('non_working_day', true);
            });

            it('marks it as such', function () {
              expect(contactData.isNonWorkingDay).toBe(true);
            });
          });

          describe('when the day is a public holiday for a contact', function () {
            beforeEach(function () {
              var publicHolidayToBe = getDayWithType('non_working_day');
              var publicHolidays = _.clone(publicHolidayData.all());

              PublicHoliday.all.and.callFake(function () {
                publicHolidays.values.push({
                  id: '123456789',
                  title: 'Fake Holiday',
                  date: publicHolidayToBe.date,
                  is_active: true
                });

                return $q.resolve(publicHolidays.values);
              });

              compileComponent();
              contactData = getDayWithType('non_working_day', true);
            });

            it('marks it as such', function () {
              expect(contactData.isPublicHoliday).toBe(true);
            });
          });

          describe('when the contact has recorded a leave request on the day', function () {
            var leaveRequest, workPattern;

            beforeEach(function () {
              leaveRequest = _.clone(leaveRequestData.singleDataSuccess().values[0]);
              workPattern = _.find(workPatternMocked.getCalendar.values, function (workPattern) {
                return workPattern.contact_id === currentContact.id;
              });

              workPattern.calendar[0].date = leaveRequest.from_date;
            });

            describe('basic tests', function () {
              beforeEach(function () {
                leaveRequest.status_id = optionGroupMock.specificObject(
                  'hrleaveandabsences_leave_request_status', 'name', 'approved'
                ).value;

                contactData = commonSetup();
              });

              it('contains a reference to the leave request itself', function () {
                expect(contactData.leaveRequest).toBe(leaveRequest);
              });

              it('assigns it the colors of its absence type', function () {
                var absenceTypeColor = _.find(controller.absenceTypes, function (absenceType) {
                  return absenceType.id === leaveRequest.type_id;
                }).color;

                expect(contactData.styles).toEqual({
                  backgroundColor: absenceTypeColor,
                  borderColor: absenceTypeColor
                });
              });
            });

            describe('when the leave request is still awaiting approval', function () {
              beforeEach(function () {
                leaveRequest.status_id = optionGroupMock.specificObject(
                  'hrleaveandabsences_leave_request_status', 'name', 'awaiting_approval'
                ).value;

                contactData = commonSetup();
              });

              it('marks it as such', function () {
                expect(contactData.isRequested).toBe(true);
              });
            });

            describe('when the leave request is for half day am', function () {
              beforeEach(function () {
                leaveRequest.from_date_type = _.find(optionGroupMock.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
                  return absenceType.name === 'half_day_am';
                }).value;

                contactData = commonSetup();
              });

              it('marks it as such', function () {
                expect(contactData.isAM).toBe(true);
              });
            });

            describe('when leave request is for half day pm', function () {
              beforeEach(function () {
                leaveRequest.from_date_type = _.find(optionGroupMock.getCollection('hrleaveandabsences_leave_request_day_type'), function (absenceType) {
                  return absenceType.name === 'half_day_pm';
                }).value;

                contactData = commonSetup();
              });

              it('marks it as such', function () {
                expect(contactData.isPM).toBe(true);
              });
            });

            describe('when the balance change of the leave request is positive', function () {
              beforeEach(function () {
                leaveRequest.balance_change = 2;

                contactData = commonSetup();
              });

              it('marks it as such', function () {
                expect(contactData.isAccruedTOIL).toBe(true);
              });
            });

            function commonSetup () {
              var day;

              LeaveRequest.all.and.callFake(function () {
                return $q.resolve({ list: [leaveRequest] });
              });

              compileComponent();

              controller.months.forEach(function (month) {
                month.days.forEach(function (dayObj) {
                  if (dayObj.date === leaveRequest.from_date) {
                    day = dayObj;
                  }
                });
              });

              return day.contactsData[currentContact.id];
            }
          });

          function getDayWithType (dayType, returnContactData) {
            var day;

            controller.months.forEach(function (month) {
              month.days.forEach(function (dayObj) {
                if (dayObj.date === helper.getDate(dayType).date) {
                  day = dayObj;
                }
              });
            });

            return returnContactData ? day.contactsData[currentContact.id] : day;
          }
        });
      });

      describe('selected months watcher', function () {
        beforeEach(function () {
          LeaveRequest.all.calls.reset();
        });

        describe('when some other months are selected', function () {
          beforeEach(function () {
            controller.selectedMonths = [1, 2, 3];
            $rootScope.$digest();
          });

          it('loads the leave requests of the added months', function () {
            expect(LeaveRequest.all.calls.count()).toBe(2);
          });
        });

        describe('when none of the months are selected', function () {
          beforeEach(function () {
            controller.selectedMonths = [];
            $rootScope.$digest();
          });

          it('loads the leave requests for all the remaining months', function () {
            var startDate = moment(controller.selectedPeriod.start_date);
            var endDate = moment(controller.selectedPeriod.end_date);

            expect(LeaveRequest.all.calls.count()).toBe(endDate.diff(startDate, 'months'));
          });
        });
      });

      describe('labelPeriod()', function () {
        var label, period;

        describe('when the period is current', function () {
          beforeEach(function () {
            period = _(controller.absencePeriods).find(function (period) {
              return period.current;
            });
            label = controller.labelPeriod(period);
          });

          it('adds Current Period to the label', function () {
            expect(label).toBe('Current Period (' + period.title + ')');
          });
        });

        describe('when the period is not current', function () {
          beforeEach(function () {
            period = _(controller.absencePeriods).filter(function (period) {
              return !period.current;
            }).sample();
            label = controller.labelPeriod(period);
          });

          it('returns the title as it is', function () {
            expect(label).toBe(period.title);
          });
        });
      });

      describe('refresh()', function () {
        describe('basic tests', function () {
          beforeEach(function () {
            Calendar.get.calls.reset();
            LeaveRequest.all.calls.reset();

            controller.refresh();
            $rootScope.$digest();
          });

          it('reloads the calendars', function () {
            expect(Calendar.get).toHaveBeenCalled();
          });

          it('reloads the leave requests', function () {
            expect(LeaveRequest.all).toHaveBeenCalled();
          });
        });

        describe('loading flag', function () {
          beforeEach(function () {
            controller.refresh();
          });

          it('does not mark the entire page as loading', function () {
            expect(controller.loading.page).not.toBe(true);
          });

          it('marks the calendar content as loading', function () {
            expect(controller.loading.calendar).toBe(true);
          });

          it('takes the calendar out of the loading phase once done', function () {
            $rootScope.$digest();
            expect(controller.loading.calendar).toBe(false);
          });
        });

        describe('source of refresh', function () {
          var oldMonths, spyLoadContacts;

          beforeEach(function () {
            spyLoadContacts = spyOnSubCtrlLoadContacts();

            compileComponent();
            oldMonths = controller.months;

            spyLoadContacts.calls.reset();
          });

          describe('when the source of the refresh is a period change', function () {
            beforeEach(function () {
              controller.refresh('period');
              $rootScope.$digest();
            });

            it('rebuilds the months structure', function () {
              expect(controller.months).not.toBe(oldMonths);
            });

            it('does not reloads the contacts', function () {
              expect(spyLoadContacts).not.toHaveBeenCalled();
            });
          });

          describe('when the source of the refresh is a contact filters change', function () {
            beforeEach(function () {
              controller.refresh('contacts');
              $rootScope.$digest();
            });

            it('does not rebuild the months structure', function () {
              expect(controller.months).toBe(oldMonths);
            });

            it('reloads the contacts', function () {
              expect(spyLoadContacts).toHaveBeenCalled();
            });
          });
        });
      });

      describe('event listeners', function () {
        describe('when a leave request is deleted', function () {
          var leaveRequestToDelete;

          beforeEach(function () {
            leaveRequestToDelete = getLeaveRequestOfCurrentlySelectedMonth();

            LeaveRequest.all.calls.reset();
            $rootScope.$emit('LeaveRequest::deleted', leaveRequestToDelete);
            $rootScope.$digest();
          });

          it('does not re-fetch the leave requests from the backend', function () {
            expect(LeaveRequest.all).not.toHaveBeenCalled();
          });

          it('removes the leave request from the list of the contact\'s leave requests', function () {
            expect(_.find(controller.leaveRequests[currentContact.id], function (leaveRequest) {
              return leaveRequestToDelete.id === leaveRequest.id;
            })).toBeUndefined();
          });

          it('resets the properties of each day that the leave request spans', function () {
            expect(getLeaveRequestDays(leaveRequestToDelete).every(isDayContactDataNull)).toBe(true);
          });
        });

        describe('when a leave request is added', function () {
          var leaveRequestToAdd;

          beforeEach(function () {
            leaveRequestToAdd = _.clone(getLeaveRequestOfCurrentlySelectedMonth());
            leaveRequestToAdd = modifyLeaveRequestData(leaveRequestToAdd, true);

            LeaveRequest.all.calls.reset();
            $rootScope.$emit('LeaveRequest::new', leaveRequestToAdd);
            $rootScope.$digest();
          });

          it('does not re-fetch the leave requests from the backend', function () {
            expect(LeaveRequest.all).not.toHaveBeenCalled();
          });

          it('adds the leave request to the list of the contact\'s leave requests', function () {
            expect(_.find(controller.leaveRequests[currentContact.id], function (leaveRequest) {
              return leaveRequestToAdd.id === leaveRequest.id;
            })).toBeDefined();
          });

          it('updates the properties of each day that the leave request spans', function () {
            expect(getLeaveRequestDays(leaveRequestToAdd).every(isDayContactDataNull)).toBe(false);
          });
        });

        describe('when a leave request is updated and its dates have changed', function () {
          var leaveRequestToUpdate, oldDays, newDays;

          beforeEach(function () {
            leaveRequestToUpdate = _.clone(getLeaveRequestOfCurrentlySelectedMonth());
            leaveRequestToUpdate = modifyLeaveRequestData(leaveRequestToUpdate);

            oldDays = getDaysFromDatesOfIndexedLeaveRequest(leaveRequestToUpdate);

            LeaveRequest.all.calls.reset();

            $rootScope.$emit('LeaveRequest::edit', leaveRequestToUpdate);
            $rootScope.$digest();

            newDays = getDaysFromDatesOfIndexedLeaveRequest(leaveRequestToUpdate);
          });

          it('does not re-fetch the leave requests from the backend', function () {
            expect(LeaveRequest.all).not.toHaveBeenCalled();
          });

          it('does not index the leave request by the old dates anymore', function () {
            expect(newDays.map(function (day) {
              return day.date;
            })).not.toEqual(oldDays.map(function (day) {
              return day.date;
            }));
          });

          it('indexes the leave request by the new dates', function () {
            expect(newDays.map(function (day) {
              return day.date;
            })).toEqual(leaveRequestToUpdate.dates.map(function (date) {
              return date.date;
            }));
          });

          it('resets the properties of the days that the leave request does not span anymore', function () {
            expect(oldDays.every(isDayContactDataNull)).toBe(true);
          });

          it('sets the properties of the days that the leave request now spans', function () {
            expect(newDays.every(isDayContactDataNull)).toBe(false);
          });
        });

        function modifyLeaveRequestData (leaveRequest, modifyId) {
          var modified = _.assign({}, leaveRequest, {
            from_date: '2016-02-20',
            to_date: '2016-02-21',
            dates: [
              { 'id': '1', 'date': '2016-02-20' },
              { 'id': '2', 'date': '2016-02-21' }
            ]
          });

          if (modifyId === true) {
            modified.id = '1';
          }

          return modified;
        }

        function getDaysFromDatesOfIndexedLeaveRequest (leaveRequest) {
          return _(controller.leaveRequests[currentContact.id])
            .map(function (_leaveRequest_, date) {
              return leaveRequest.id === _leaveRequest_.id ? date : null;
            })
            .compact()
            .map(function (date) {
              date = moment(date);

              return _.find(controller.months[date.month()].days, function (day) {
                return day.date === date.format('YYYY-MM-DD');
              });
            })
            .value();
        }

        function getLeaveRequestOfCurrentlySelectedMonth () {
          return _(controller.leaveRequests[currentContact.id])
            .find(function (leaveRequest) {
              return _.includes(controller.selectedMonths, moment(leaveRequest.from_date).month());
            });
        }

        function getLeaveRequestDays (leaveRequest) {
          var days = [];
          var pointerDate = moment(leaveRequest.from_date).clone();
          var toDate = moment(leaveRequest.to_date);

          while (pointerDate.isSameOrBefore(toDate)) {
            days.push(_.find(controller.months[pointerDate.month()].days, function (day) {
              return day.date === pointerDate.format('YYYY-MM-DD');
            }));

            pointerDate.add(1, 'day');
          }

          return days;
        }

        function isDayContactDataNull (day) {
          var contactData = day.contactsData[currentContact.id];

          return contactData.leaveRequest === null &&
            contactData.styles === null &&
            contactData.isAccruedTOIL === null &&
            contactData.isRequested === null &&
            contactData.isAM === null &&
            contactData.isPM === null;
        }
      });

      function amend2016Period (params) {
        AbsencePeriod.all.and.callFake(function () {
          var absencePeriods = _.clone(absencePeriodData.all().values);
          _.assign(absencePeriods[0], params);

          return $q.resolve(absencePeriods);
        });
      }

      function compileComponent (skipDigest, bindings) {
        controller = $componentController('leaveCalendar', null, _.assign({ contactId: currentContact.id }, bindings));
        skipDigest !== true && $rootScope.$digest();
      }

      function spyOnSubCtrlLoadContacts () {
        var ctrlName = 'LeaveCalendar' + _.capitalize(currentContact.role) + 'Controller';
        var realSubCtrl = $controller(ctrlName).init(controller);
        var spy = jasmine.createSpy('loadContacts').and.callFake(function () {
          return realSubCtrl.loadContacts();
        });

        $controllerProvider.register(ctrlName, function () {
          return {
            init: function () {
              return { loadContacts: spy };
            }
          };
        });

        return spy;
      }

      function mockCheckPermissionService () {
        return jasmine.createSpy().and.callFake(function (permissionToCheck) {
          if (permissionToCheck === sharedSettings.permissions.ssp.manage) {
            return $q.resolve(currentContact.role === 'manager');
          }

          if (permissionToCheck === sharedSettings.permissions.admin.administer) {
            return $q.resolve(currentContact.role === 'admin');
          }
        });
      }
    });
  });
}(CRM));
