(function($, STUDIP) {
    STUDIP.ActivityFeed = {
        user_id : null,
        polling: null,
        initial: true,
        scrolledfrom: null,

        init: function() {
            STUDIP.ActivityFeed.loadFeed('' ,false, STUDIP.ActivityFeed.scrolledfrom);

            $('#stream-container').scroll(function () {
                if((($('#stream-container').prop('scrollHeight') < ($('#stream-container').scrollTop() + 250))
                        && !STUDIP.ActivityFeed.polling) && STUDIP.ActivityFeed.scrolledfrom ) {
                    STUDIP.ActivityFeed.loadFeed('' ,true, STUDIP.ActivityFeed.scrolledfrom);
                    STUDIP.ActivityFeed.polling = true;
                }
            });
        },

        getTemplate: _.memoize(function(name) {
            return _.template($("script." + name).html());
        }),

        loadFeed: function(filtertype, append, scrollfrom) {
            if (STUDIP.ActivityFeed.user_id === null) {
                console.log('Could not retrieve activities, no valid user id found!');
                return;
            }

            $.ajax(STUDIP.URLHelper.resolveURL('api.php/user/' + STUDIP.ActivityFeed.user_id
                    + '/activitystream?filtertype=' + filtertype + '&scrollfrom=' + scrollfrom), {
                success: function(data) {
                    var stream        = STUDIP.ActivityFeed.getTemplate('activity_stream');
                    var activity      = STUDIP.ActivityFeed.getTemplate('activity');
                    var activity_urls = STUDIP.ActivityFeed.getTemplate('activity-urls');
                    var activities    = data;


                    STUDIP.ActivityFeed.initial = false;

                    var num_entries   = Object.keys(activities).length;
                    STUDIP.ActivityFeed.polling = false;

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
