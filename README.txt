
Polyphony v. 1.4.1 (2008-08-20)
=================================

What is Polyphony?
------------------
Polyphony is a set of user-interface components to go along with the Harmoni
Framework. These components include a Wizard System, Repository access actions,
log-browsing actions, user-management forms, and a number of other user interface
components that may be useful for web-based software that is built on Harmoni.

Usage of Polyphony is not required for Harmoni usage, but most of the Polyphony
components rely on Harmoni.

Polyphony is currently used in the curricular applications built by the Curricular
Technologies group at Middlebury College: Segue 2 and Concerto.

About Harmoni:

The Harmoni Project is an effort led by the Curricular Technologies group at
Middlebury College to build an application framework and standards-based
infrastructure bus to support the rapid development and easy maintenance of
curricular it projects. The project is built entirely using PHP's OOP (Object
Oriented Programming) model, allowing the framework code to be easily extended and enhanced.

At the core of the Harmoni Application Framework is an implementation of The Open
Knowledge Initiative's (O.K.I) Open Service Interface Definitions (OSIDs). The OSIDs
are a standard service-oriented API that defines a broad set of services that are
germane to IT projects in the education field yet also fitting for broader uses.

Sitting alongside of the Harmoni services is the "Harmoni Architecture". The
"architecture" is a set of controllers and templates that coordinate configuration
of services, the proper execution of application actions, and any post-processing of
application output. The architecture is built on a popular module/action model, in
which your PHP program contains multiple modules, each of which contain multiple
executable actions. All you, as an application developer, need to write are the
action files, not the controller logic.

The "Harmoni Architecture" is separate from the Harmoni Services and either can be
used independently of the other.


Current Version Notes
---------------------
This release fixes a minor PHP notice


Downloads
---------------------
For the latest and archived versions, please download from SourceForge:

http://sourceforge.net/project/showfiles.php?group_id=82873


Documentation
---------------------
See the Harmoni wiki for online documentation:

http://harmoni.sf.net/


Bug Tracker
---------------------
https://sourceforge.net/tracker/?group_id=82873&atid=567473







===================================================================
| Prior Polyphony Release Notes
| (See the Polyphony change log for more details)
===================================================================


v. 1.4.1 (2008-08-20)
----------------------------------------------------
This release fixes a minor PHP notice



v. 1.4.0 (2008-08-14)
----------------------------------------------------
This release fixes a number of security issues.

Security issues fixed: 

- Cross-Site Request Forgeries (CSRF) are now eliminated from data-modification
actions. Read about CSRF at: http://shiflett.org/articles/cross-site-request-forgeries

- Admin actions are now restricted to prevent listing of users and ids in the
system. 



v. 1.3.3 (2008-08-13)
----------------------------------------------------
This release adds a number of fixes to the help-system to support Segue 2.



v. 1.3.2 (2008-08-07)
----------------------------------------------------
This release fixes several cross-site scripting vulnerabilities in code related to
logging and error display. See the change log for details



v. 1.3.1 (2008-08-01)
----------------------------------------------------
This release fixes a number of Javascript issues that were causing problems in Segue
2. See the change-log for details.



v. 1.3.0 (2008-07-24)
----------------------------------------------------
This release includes several updates to the Javascript library and Wizard to
support Segue 2. See the change-log for details.



v. 1.2.12 (2008-07-22)
----------------------------------------------------
This release fixes an issue that was causing problems in Concerto.



v. 1.2.11 (2008-07-21)
----------------------------------------------------
This release fixes an issue with file uploads under safe mode.



v. 1.2.10 (2008-07-17)
----------------------------------------------------
This release fixes an issue with theme CSS loading that was preventing theme options
from taking effect.



v. 1.2.9 (2008-07-17)
----------------------------------------------------
This release fixes a few bugs to support Segue and Concerto.



v. 1.2.8 (2008-07-14)
----------------------------------------------------
This release fixes a minor issue with log-browsing.



v. 1.2.7 (2008-07-10)
----------------------------------------------------
This release adds a few minor fixes to support Segue.



v. 1.2.6 (2008-06-16)
----------------------------------------------------
This release adds a new multiple checkbox wizard component.



v. 1.2.5 (2008-06-13)
----------------------------------------------------
This release adds some minor improvements.



v. 1.2.4 (2008-06-09)
----------------------------------------------------
This release add new user interfaces used for visitor registration.



v. 1.2.3 (2008-06-03)
----------------------------------------------------
This release fixes a few issues that were affecting Concerto's import and export
functions. 



v. 1.2.2 (2008-05-23)
----------------------------------------------------
Missing theme images now do not fill the logs with errors.



v. 1.2.1 (2008-05-22)
----------------------------------------------------
A parse error had snuck into the thumbnail viewing action. This is now fixed.



v. 1.2.0 (2008-05-20)
----------------------------------------------------
This release adds support for the new Gui2 theming in Harmoni and used by Segue 2.
Also added are a number of new Wizard components and improvements.



v. 1.1.0 (2008-04-11)
----------------------------------------------------
This release add a few minor changes to tagging actions.



v. 1.0.6 (2008-04-03)
----------------------------------------------------
This release adds a minor change needed by Segue 2 - beta 18.



v. 1.0.5 (2008-03-26)
----------------------------------------------------
This release adds a few javascript functions.



v. 1.0.4 (2008-03-25)
----------------------------------------------------
This release fixes a minor syntax error in the wizard.



v. 1.0.3 (2008-03-12)
----------------------------------------------------
Fixes to a few bugs.



v. 1.0.2 (2008-03-10)
----------------------------------------------------
This release fixes a number of bugs, particularly with respect to cross-browser
compatibility and Repository Importer issues.



v. 1.0.1 (2008-02-21)
----------------------------------------------------
This release adds the ability of the Rich-Text editor in the wizard to accept custom
configuration options.



v. 1.0.0 (2008-02-15)
----------------------------------------------------
This release includes a few minor improvements and has been updated to work with
Harmoni 1.0.0.



v. 0.10.7 (2008-01-14)
----------------------------------------------------
New Wizard abilities.



v. 0.10.6 (2007-12-20)
----------------------------------------------------
This release fixes a few minor bugs.



v. 0.10.5 (2007-12-12)
----------------------------------------------------
This release fixes a few minor bugs and adds a new Wizard component.



v. 0.10.4 (2007-11-29)
----------------------------------------------------
This release fixes a number of wizard issues.



v. 0.10.3 (2007-11-09)
----------------------------------------------------
This release fixes a few bugs and adds a new Wizard component.



v. 0.10.2 (2007-11-01)
----------------------------------------------------
This release includes some minor bug fixes and cleanup.



v. 0.10.1 (2007-10-24)
----------------------------------------------------
This release fixes issues with log browsing, admin tools, and tags.



v. 0.10.0 (2007-10-22)
----------------------------------------------------
This release fixes a number of bugs and reworks many of the class hierarchies to use
PHP 5 interfaces and abstract classes for better enforcement of class responsibilities.

The Action class hierarchy has changed to use abstract classes and interfaces. Any
applications that extend the Action class will need to be checked to ensure that
they implement all abstract methods.



v. 0.9.3 (2007-09-25)
----------------------------------------------------




v. 0.9.2 (2007-09-20)
----------------------------------------------------




v. 0.9.1 (2007-09-13)
----------------------------------------------------




v. 0.9.0 (2007-09-07)
----------------------------------------------------




v. 0.8.2 (2007-04-10)
----------------------------------------------------




v. 0.8.1 (2007-04-05)
----------------------------------------------------




v. 0.8.0 (2006-12-13)
----------------------------------------------------




v. 0.7.0 (2006-12-01)
----------------------------------------------------




v. 0.6.9 (2006-11-30)
----------------------------------------------------




v. 0.6.8 (2006-11-28)
----------------------------------------------------




v. 0.6.7 (2006-10-25)
----------------------------------------------------




v. 0.6.6 (2006-08-16)
----------------------------------------------------




v. 0.6.5 (2006-08-15)
----------------------------------------------------




v. 0.6.4 (2006-08-11)
----------------------------------------------------




v. 0.6.3 (2006-08-04)
----------------------------------------------------




v. 0.6.2 (2006-08-02)
----------------------------------------------------




v. 0.6.1 (2006-07-21)
----------------------------------------------------




v. 0.6.0 (2006-06-16)
----------------------------------------------------




v. 0.5.2 (2006-05-26)
----------------------------------------------------




v. 0.5.1 (2006-05-19)
----------------------------------------------------




v. 0.5.0 (2006-05-05)
----------------------------------------------------




v. 0.4.1 (2005-02-09)
----------------------------------------------------




v. 0.4.0 (2005-01-10)
----------------------------------------------------




v. 0.3.0 (2005-10-12)
----------------------------------------------------




v. 0.2.2 (2005-04-14)
----------------------------------------------------




v. 0.2.1 (2005-04-11)
----------------------------------------------------




v. 0.2.0 (2005-04-07)
----------------------------------------------------




v. 0.1.0 (2004-10-26)
----------------------------------------------------




