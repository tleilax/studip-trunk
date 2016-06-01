<script type="text/template" class="activity_stream">
    <%- activity.id %>
    <% if (num_entries > 0) {
        var last_date;

        _.each(stream, function(act) { %>
            <%
            var new_date = new Date(act.mkdate * 1000);
            var new_date_string = ('0' + new_date.getDate()).slice(-2) + '.'
                                + ('0' + (new_date.getMonth()+1)).slice(-2) + '.'
                                + new_date.getFullYear();
            if (last_date !=  new_date_string) { %>
                <% last_date = new_date_string; %>
                <span class="activity-day"><%- last_date %></span>
            <% } %>
            <%= activity({
                    activity      : act,
                    user_id       : user_id,
                    activity_urls : activity_urls
                }) %>
        <% });
    } else { %>
        <?= MessageBox::info(_('Keine Aktivitäten gefunden.')) ?>
    <% } %>
</script>

<script type="text/template" class="activity">
    <section class="activity <% if (activity.actor.id == user_id) { %>right<% } else { %>left<% } %>">
        <header>
            <span class="provider_circle">
            <% if (activity.provider === 'blubber') { %>
                <img src="<%- STUDIP.ASSETS_URL + 'images/icons/32/white/blubber.png'  %>">
            <% } else if(activity.provider === 'documents') { %>
                <img src="<%- STUDIP.ASSETS_URL + 'images/icons/32/white/files.png'  %>">
            <% } else if(activity.provider === 'forum') { %>
                <img src="<%- STUDIP.ASSETS_URL + 'images/icons/32/white/forum.png'  %>">
            <% } else if(activity.provider === 'literature') { %>
            <img src="<%- STUDIP.ASSETS_URL + 'images/icons/32/white/literature.png'  %>">
            <% } else if(activity.provider === 'message') { %>
                <img src="<%- STUDIP.ASSETS_URL + 'images/icons/32/white/mail.png'  %>">
            <% } else if(activity.provider === 'news') { %>
                <img src="<%- STUDIP.ASSETS_URL + 'images/icons/32/white/news.png'  %>">
            <% } else if(activity.provider === 'participants') { %>
                <img src="<%- STUDIP.ASSETS_URL + 'images/icons/32/white/persons.png'  %>">
            <% } else if(activity.provider === 'schedule') { %>
                <img src="<%- STUDIP.ASSETS_URL + 'images/icons/32/white/schedule.png'  %>">
            <% } else if(activity.provider === 'wiki') { %>
                <img src="<%- STUDIP.ASSETS_URL + 'images/icons/32/white/wiki.png'  %>">
            <% } else { %>
                <img src="<%- STUDIP.ASSETS_URL + 'images/icons/32/white/activity.png'  %>">
            <% } %>
            </span>
            <h1>
                <%- activity.title %>
            </h1>
        </header>
        <section class="activity-content">
            <section class="activity-description">
                <span class="activity-date">
                    <%- new Date(activity.mkdate * 1000).toLocaleString() %>
                </span>

                <span class="activity-details">
                    <%= activity.content %>
                </span>
            </section>
            <div class='clear'></div>
        </section>
        <footer>
                <span class="activity-object-link">
                    <%= activity_urls({urls: activity.object_url}) %>
                </span>
                <span class="activity-avatar-container-footer">
                    <a href="<%- STUDIP.URLHelper.resolveURL('dispatch.php/profile?username=' + activity.actor.details.name.username) %>">
                        <img src="<%- activity.actor.details.avatar_small  %>">
                    </a>
                </span>
        </footer>

    </section>
</script>

<script type="text/template" class="activity-urls">
    <ul>
    <% _.each(urls, function(name, link) { %>
        <li>
            <a href="<%= link %>">
                <%- name %>
            </a>
        </li>
    <% }) %>
    </ul>
</script>
