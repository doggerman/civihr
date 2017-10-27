/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'mocks/data/absence-type-data',
  'mocks/data/leave-request-data',
  'mocks/data/option-group-mock-data',
  'leave-absences/shared/components/leave-calendar-day.component',
  'leave-absences/manager-leave/app'
], function (_, moment, absenceTypeData, leaveRequestData, optionGroupData) {
  'use strict';

  describe('leaveCalendarDay', function () {
    var $componentController, $log, $rootScope, absenceTypes, calculationUnits,
      controller, dayTypes, LeavePopup, leaveRequest;

    beforeEach(module('manager-leave'));
    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_,
    _LeavePopup_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      absenceTypes = absenceTypeData.all().values;
      calculationUnits = optionGroupData.getCollection(
        'hrleaveandabsences_absence_type_calculation_unit');
      dayTypes = optionGroupData.getCollection(
        'hrleaveandabsences_leave_request_day_type');
      LeavePopup = _LeavePopup_;
      leaveRequest = leaveRequestData.all().values[0];

      spyOn($log, 'debug');
      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('mapping leave request fields', function () {
      var absenceType, fromDateType, toDateType;

      beforeEach(function () {
        controller.contactData.leaveRequest = leaveRequest;
        absenceType = _.find(absenceTypes, function (type) {
          return +type.id === +leaveRequest.type_id;
        });
        fromDateType = optionGroupData.specificObject(
          'hrleaveandabsences_leave_request_day_type', 'value',
          leaveRequest.from_date_type);
        toDateType = optionGroupData.specificObject(
          'hrleaveandabsences_leave_request_day_type', 'value',
          leaveRequest.from_date_type);

        $rootScope.$digest();
      });

      it('maps the absence type title', function () {
        expect(leaveRequest['type_id.title']).toEqual(absenceType.title);
      });

      it('maps the from date type label', function () {
        expect(leaveRequest['from_date_type.label']).toEqual(fromDateType.label);
      });

      it('maps the to date type label', function () {
        expect(leaveRequest['from_date_type.label']).toEqual(toDateType.label);
      });
    });

    describe('selecting a tooltip template', function () {
      var absenceType, calculationUnitInDays, calculationUnitInHours,
        nextWeek;

      beforeEach(function () {
        absenceType = _.find(absenceTypes, function (type) {
          return +type.id === +leaveRequest.type_id;
        });
        calculationUnitInDays = optionGroupData.specificObject(
          'hrleaveandabsences_absence_type_calculation_unit', 'name',
          'days').value;
        calculationUnitInHours = optionGroupData.specificObject(
          'hrleaveandabsences_absence_type_calculation_unit', 'name',
          'hours').value;
        nextWeek = moment(leaveRequest.from_date).add(7, 'days')
          .format('YYYY-MM-DD HH:ii:ss');
        controller.contactData.leaveRequest = leaveRequest;
      });

      describe('when the request is for a single day and the calculation unit is in hours', function () {
        beforeEach(function () {
          absenceType.calculation_unit = calculationUnitInHours;
          leaveRequest.to_date = leaveRequest.from_date;

          $rootScope.$digest();
        });

        it('selects the tooltip template for unit type hours on single date', function () {
          expect(controller.tooltipTemplate).toBe('type-hours-on-single-date-tooltip');
        });
      });

      describe('when the request is for multiple days and the calculation unit is in hours', function () {
        beforeEach(function () {
          absenceType.calculation_unit = calculationUnitInHours;
          leaveRequest.to_date = nextWeek;

          $rootScope.$digest();
        });

        it('selects the tooltip template for unit type hours on multiple dates', function () {
          expect(controller.tooltipTemplate).toBe('type-hours-on-multiple-dates-tooltip');
        });
      });

      describe('when the request is for a single day and the calculation unit is in days', function () {
        beforeEach(function () {
          absenceType.calculation_unit = calculationUnitInDays;
          leaveRequest.to_date = leaveRequest.from_date;

          $rootScope.$digest();
        });

        it('selects the tooltip template for unit type days on single date', function () {
          expect(controller.tooltipTemplate).toBe('type-days-on-single-date-tooltip');
        });
      });

      describe('when the request is for multiple days and the calculation unit is in days', function () {
        beforeEach(function () {
          absenceType.calculation_unit = calculationUnitInDays;
          leaveRequest.to_date = nextWeek;

          $rootScope.$digest();
        });

        it('selects the tooltip template for unit type days on multiple dates', function () {
          expect(controller.tooltipTemplate).toBe('type-days-on-multiple-dates-tooltip');
        });
      });
    });

    describe('openLeavePopup()', function () {
      var event;
      var leaveRequest = { key: 'value' };
      var leaveType = 'some_leave_type';
      var selectedContactId = '101';
      var isSelfRecord = true;

      beforeEach(function () {
        event = jasmine.createSpyObj('event', ['stopPropagation']);
        spyOn(LeavePopup, 'openModal');
        controller.openLeavePopup(event, leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });

      it('opens the leave request popup', function () {
        expect(LeavePopup.openModal).toHaveBeenCalledWith(leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });

      it('stops the event from propagating', function () {
        expect(event.stopPropagation).toHaveBeenCalled();
      });
    });

    /**
     * Compiles and stores the component instance. Passes the contact and
     * support data to the controller.
     */
    function compileComponent () {
      var $scope = $rootScope.$new();

      controller = $componentController('leaveCalendarDay', {
        $scope: $scope
      }, {
        contactData: {},
        supportData: {
          absenceTypes: absenceTypes,
          dayTypes: dayTypes,
          calculationUnits: calculationUnits
        }
      });
    }
  });
});
