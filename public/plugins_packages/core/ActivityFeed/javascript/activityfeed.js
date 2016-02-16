(function($, STUDIP) {
    STUDIP.ActivityFeed = {
        user_id : null,
        
        init: function() {
            STUDIP.ActivityFeed.loadFeed(Math.floor(Date.now() / 1000) - (24 * 3600 * 10), Math.floor(Date.now() / 1000), false);
        },
        
        getTemplate: _.memoize(function(name) {
            return _.template($("script." + name).html());
        }),
        
        loadFeed: function(from, to, append) {
            if (STUDIP.ActivityFeed.user_id === null) {
                console.log('Could not retrieve activities, no valid user id found!');
                return;
            }
            
            $.ajax(STUDIP.URLHelper.resolveURL('api.php/user/' + STUDIP.ActivityFeed.user_id + '/activitystream?start=' + from + '&end=' + to), {
                success: function(data) {
                    var stream        = STUDIP.ActivityFeed.getTemplate('activity_stream');
                    var activity      = STUDIP.ActivityFeed.getTemplate('activity');
                    var activity_urls = STUDIP.ActivityFeed.getTemplate('activity-urls');
                    var num_entries   = Object.keys(data).length;

                    if (!append) {                                              // replace data in DOM
                        $('#stream-container').html('');
                    }

                    $('#stream-container').append(stream({
                        stream        : data,
                        num_entries   : num_entries,
                        activity      : activity,
                        activity_urls : activity_urls,
                        user_id       :  STUDIP.ActivityFeed.user_id
                    }));
                }
            });
        }
    };    
})(jQuery, STUDIP);

