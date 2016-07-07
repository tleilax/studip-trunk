(function($, STUDIP) {
    STUDIP.ActivityFeed = {
        user_id : null,
        start_date : null,
        end_date : null,
        polling: null,
        offset: 0,
        limit: 10,
        initial: true,

        init: function() {
            STUDIP.ActivityFeed.loadFeed(STUDIP.ActivityFeed.offset, STUDIP.ActivityFeed.limit, STUDIP.ActivityFeed.start_date, STUDIP.ActivityFeed.end_date, '' ,false, '');

            $('#stream-container').scroll(function () {
                if((($('#stream-container').prop('scrollHeight') < ($('#stream-container').scrollTop() + 250)) && !STUDIP.ActivityFeed.polling) && STUDIP.ActivityFeed.scrolledfrom ) {
                    STUDIP.ActivityFeed.loadFeed(STUDIP.ActivityFeed.offset, STUDIP.ActivityFeed.limit, STUDIP.ActivityFeed.start_date, STUDIP.ActivityFeed.end_date, '' ,true, STUDIP.ActivityFeed.scrolledfrom);
                    STUDIP.ActivityFeed.polling = true;
                }
            });
        },

        getTemplate: _.memoize(function(name) {
            return _.template($("script." + name).html());
        }),

        loadFeed: function(offset, limit, from, to, filtertype, append, scrollfrom) {
            if (STUDIP.ActivityFeed.user_id === null) {
                console.log('Could not retrieve activities, no valid user id found!');
                return;
            }

            $.ajax(STUDIP.URLHelper.resolveURL('api.php/user/' + STUDIP.ActivityFeed.user_id + '/activitystream?offset=' + offset + '&limit=' + limit + '&start=' + from + '&end=' + to + '&filtertype=' + filtertype + '&scrollfrom=' + scrollfrom), {
                success: function(data) {
                    var stream        = STUDIP.ActivityFeed.getTemplate('activity_stream');
                    var activity      = STUDIP.ActivityFeed.getTemplate('activity');
                    var activity_urls = STUDIP.ActivityFeed.getTemplate('activity-urls');
                    var activities    = data.collection;


                    STUDIP.ActivityFeed.initial = false;

                    var num_entries   = Object.keys(activities).length;
                    STUDIP.ActivityFeed.polling = false;
                    STUDIP.ActivityFeed.offset += limit;

                    var lastelem = $(activities).last();
                    
                    if(lastelem[0]) {
                        STUDIP.ActivityFeed.scrolledfrom  = lastelem[0].mkdate;
                    } else STUDIP.ActivityFeed.scrolledfrom = false;

                    if (!append) {            // replace data in DOM
                        $('#stream-container').html('');
                    }


                    $('#stream-container').append(stream({
                        stream        : activities,
                        num_entries   : num_entries,
                        activity      : activity,
                        activity_urls : activity_urls,
                        user_id       :  STUDIP.ActivityFeed.user_id
                    }));

                    $('.provider_circle').click(function() {
                        $(this).parent().parent().children('.activity-content').toggle();
                    });
                }
            });

        },

        update : function(html) {
            $('#afeed').replaceWith(html);

        }
    };
})(jQuery, STUDIP);

