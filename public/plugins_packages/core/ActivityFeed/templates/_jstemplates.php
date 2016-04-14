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
            <h1>
                <%- activity.title %>
            </h1>
        </header>
        <section class="activity-content">
            <!-- TODO: Avatar-URL mitliefern (von der Rest-Route) -->
            <div class="activity-avatar-container">
                <a href="<%- STUDIP.URLHelper.resolveURL('dispatch.php/profile?username=' + activity.actor.details.name) %>">
                    <img src="<%- activity.actor.details.avatar_medium  %>">
                </a>
            </div>
            <section class="activity-description">
                <span class="activity-date">
                    <%- new Date(activity.mkdate * 1000).toLocaleString() %>
                </span>

                <span class="activity-details">
                    <%= activity.content %>

                    <!-- TODO: fade out at the bottom to signalize further content -->
                </span>

                <span class="activity-object-link">
                    <%= activity_urls({urls: activity.object_url}) %>
                </span>
            </section>
            <div class='clear'></div>
        </section>
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
