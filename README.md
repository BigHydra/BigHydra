BigHydra Report Tool
========================

The intention behind this tool is to automate the boring daily non-coding tasks.
It starts with reports and will do smaller tasks for us later on.

To save time filling out the weekly time tracking report it is retrieving Jira tickets, processes the time logging and sends out an email report.

Over time we will add more features.


### Roadmap

- [x] basic Jira sync to Mongo db
- [x] weekly time logging report, to fill out our company time tracking tool
- [x] comment analysis for users mentioning other users (@jira.user / [~jira.user])
- [ ] git log sync to Mongo db
- [ ] git log report, to fill out the Jira time logging


Installation
----------------------------------

Clone the repository

    git clone https://github.com/BigHydra/BigHydra.git

Run [Composer][4] install

    composer install

Fill out the settings, mysql is not needed currently.


Verify your Jira connection, you will be asked for your password

    app/console hydra:jira-rest-api:test [host] [user]


Sync your Jira data
----------------------------------

Run the following command with your host and username.
You will be asked to enter your password.

    app/console hydra:jira:sync http://jira.atlassian.com [username]

You will see an output about the synced issues.


### Automated sync

If you like to setup a scheduled sync every hour or once a day, please configure your credentials in your `parameters.yml`.

    jira.auth.host:         http://jira.atlassian.com
    jira.auth.username:     demo
    jira.auth.password:     yourSecretPassword

Once everything is configured just add the following command to your cron job file

    app/console hydra:jira:credentials-from-config-sync


Create a report
----------------------------------

Once you have synced your Jira data, you can run reports based on these data.

    app/console hydra:report:weekly [week-number] [jira-user]

Valid values for `week-number` are

  * 1 - 52, calendar week numbers
  * `CW`, for current week
  * `LW`, for last week

Be careful, this command is actually sending an email to the jira-user and the configured cc recipients.
 Make sure you enabled the debug mode to be the only recipient while testing.


How is it done?
----------------------------------

The application is using [MongoDB][1] to store raw data.
Using the [MongoDB Aggregation Framework][2] we can execute all kinds of queries and export them as report.
To access Jira it is using the [Jira REST API][2]

Using a schemaless database is a big advantage when dealing with nested json structures returned by REST APIs.

The workflow looks like this

  * extract the data from different sources, e.g. REST API
  * transform the date, do minimal adjustments
  * load into MongoDB

The code needed for these 3 steps is very minimal, assuming you can use a good library to access the sources.

Next comes the analysing and publishing of the reports.

* define what report you like to build
* figure out how to query MongoDB
* store the result as csv or send it via email.

That's it.

[1]: http://www.mongodb.org/
[2]: http://docs.mongodb.org/manual/aggregation/
[3]: https://developer.atlassian.com/display/JIRADEV/JIRA+REST+APIs
[4]: https://getcomposer.org/download/
