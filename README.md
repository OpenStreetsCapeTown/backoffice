# Open Streets Cape Town Back Office System

The back office system allows Open Streets Cape Town to easily and efficiently manage information, plan events, and communicate with stakeholders. Among other things, the system provides these functionalities:

- Profiles of people and organizations including:
  - contact information
  - log of event attendance and interaction
  - skills and other tags
  - mailing list signups
- Filters to generate lists of relevant people and organizations based on tags, skills, and relationships with events
- Option to manage events (Open Street events but also other events like community meetings or walks), including:
  - dashboard with quick access to principal information
  - checklist of things to do
  - related sub-events (planning meetings etc.)
  - all related volunteers, stakeholders, and other people
- Option to send targeted e-mail campaigns (using Mailchimp's API)
- Discussion whiteboard

# Screenshots

Some sample screenshots of the back office system:

![img](http://friends.openstreets.co.za/img/screenshots/planning.png "Planning")
![img](http://friends.openstreets.co.za/img/screenshots/quickadd.png "Quick add several people at once")
![img](http://friends.openstreets.co.za/img/screenshots/filters.png "Filtering users in the system")

# Help develop this system!

If you'd like to assist in the development of this system, please get in touch. You are welcome to clone this repository and give it a try on your own machine. Some small configurations may be necessary to get things up and running - especially to create an own user first. We welcome programmers to help with the development, but we also welcome volunteers who want to help enter data and further digitize the information that we have available at Open Streets Cape Town.

# Running this system on your local machine

To run this on your own machine, take the following steps:

- Clone the repository
- Rename 'config.sample.php' to 'config.php'
- Open 'config.php' and change the variables and constants to match your environment
- For a development machine, the only really required constants are the PATH, URL and CONNECTION settings
- In your connection script make sure you configure your database name, username, and password (sample settings are included in connections/connection.sample.php)
- Save your connection file as connection.php (or give it any other name, but just make sure you refer to this file in the config.php file)

# Use this for your own organization

There are various Open Streets organizations around the world. We imagine our system could be of benefit to these organizations as well. If you would like to use this system, please feel free to clone / fork the project. It may be best to reach out first so we can give you some pointers as to how to configure this for your own purposes. We can also work on this same project together and develop one single project that can be used by various organizations at the same time. 
