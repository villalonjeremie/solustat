$(function () {
    $('#calendar-holder').fullCalendar({
        header: {
            left: 'prev, next',
            center: 'title',
            right: 'month, agendaWeek, agendaDay'
        },
        timezone: ('America/Blanc-Sablon'),

        allDaySlot: false,
        defaultView: 'agendaWeek',
        lazyFetching: true,
        firstDay: 1,
        selectable: true,
        timeFormat: {
            agenda: 'h:mmt',
            '': 'h:mmt'
        },
        editable: true,
        eventDurationEditable: true,
        eventSources: [
            {
                url: '/app_dev.php/full-calendar/load',
                type: 'POST',
                data: {
                    filters: {}
                },
                error: function () {
                    //alert()
                }
            }
        ]
    })
});