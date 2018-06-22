$(document).ready(function(){
        var calendar = $('#calendar').fullCalendar({
            header:{
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            defaultView: 'agendaWeek',
            editable: true,
            selectable: true,
            allDaySlot: true,
            
            events: "/app_dev.php/full-calendar/load",

            eventClick:  function(event, jsEvent, view) {
                endtime = $.fullCalendar.moment(event.end).format('h:mm');
                starttime = $.fullCalendar.moment(event.start).format('dddd, MMMM Do YYYY, h:mm');
                var mywhen = starttime + ' - ' + endtime;
                $('#modalTitle').html(event.title);
                $('#modalWhen').text(mywhen);
                console.log(event.id);
                $('#eventID').val(event.id);
                $('#calendarModal').modal();
            },
            
            //header and other values
            select: function(start, end, jsEvent) {
                endtime = $.fullCalendar.moment(end).format('h:mm');
                starttime = $.fullCalendar.moment(start).format('dddd, MMMM Do YYYY, h:mm');
                var mywhen = starttime;
                start = moment(start).format();
                end = moment(end).format();
                $('#createEventModal #startTime').val(start);
                $('#createEventModal #endTime').val(end);
                $('#createEventModal #when').text(mywhen);
                $('#createEventModal').modal('toggle');
           },
           eventDrop: function(event, delta){
               $.ajax({
                   url: '/app_dev.php/full-calendar/load',
                   data: 'action=update&title='+event.title+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&id='+event.id ,
                   type: "POST",
                   success: function(json) {
                   //alert(json);
                   },
                   beforeSend: function(){
                       $('.loader').show()
                   },
                   complete: function(){
                       $('.loader').hide();
                   }
               });
           },
           eventResize: function(event) {
               $.ajax({
                   url: '/app_dev.php/full-calendar/load',
                   data: 'action=update&title='+event.title+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&id='+event.id,
                   type: "POST",
                   success: function(json) {
                       //alert(json);
                   },
                   beforeSend: function(){
                       $('.loader').show()
                   },
                   complete: function(){
                       $('.loader').hide();
                   }
               });
           }
        });
               
       $('#submitButton').on('click', function(e){
           e.preventDefault();
           doSubmit();
       });
       
       $('#deleteButton').on('click', function(e){
           e.preventDefault();
           doDelete();
       });

        $('#unlinkButton').on('click', function(e){
            e.preventDefault();
            doUnlink();
        });
       
       function doDelete(){
           $("#calendarModal").modal('hide');
           var eventID = $('#eventID').val();
           $.ajax({
               url: '/app_dev.php/full-calendar/load',
               data: 'action=delete&id='+eventID,
               type: "POST",
               success: function(json) {
                   if(json == 1)
                        $("#calendar").fullCalendar('removeEvents',eventID);
                   else
                        return false;
               },
               beforeSend: function(){
                   $('.loader').show()
               },
               complete: function(){
                   $('.loader').hide();
               }
           });
       }

        function doUnlink(){
            $("#calendarModal").modal('hide');
            var eventID = $('#eventID').val();
            $.ajax({
                url: '/app_dev.php/full-calendar/load',
                data: 'action=unlink&id='+eventID,
                type: "POST",
                success: function(json) {
                    if(json == 1)
                        $("#calendar").fullCalendar('removeEvents',eventID);
                    else
                        return false;
                },
                beforeSend: function(){
                    $('.loader').show()
                },
                complete: function(){
                    $('.loader').hide();
                }
            });
        }

       function doSubmit(){
            $("#createEventModal").modal('hide');
            var startTime = $('#startTime').val();
            var patientId = $('#patients option:selected').val();
            var patient = $('#patients option:selected').text();
            var visitTimeStamp = $('#visits-time option:selected').val();
            var visitTimeName = $('#visits-time option:selected').text();
            var endTimeStamp = $.fullCalendar.moment(startTime).unix() + parseInt(visitTimeStamp);
            var endTime = $.fullCalendar.moment(endTimeStamp*1000).format('YYYY-MM-DD[T]HH:mm:ss');

           $.ajax({
               url: '/app_dev.php/full-calendar/load',
               data: 'action=add&patientId='+patientId+'&start='+startTime+'&end='+endTime,
               type: "POST",
               success: function(json) {

                   $("#calendar").fullCalendar('renderEvent',
                   {
                       id: json[0].id,
                       title: json[0].title,
                       start: startTime,
                       end: endTime,
                       color: json[0].color
                   },
                   true);
               },
               beforeSend: function(){
                   $('.loader').show()
               },
               complete: function(){
                   $('.loader').hide();
               }
           });
           
       }
    });