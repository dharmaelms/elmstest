# Commit Messages
To have a clear and focused history of code changes is greatly helped
by using a consistent way of writing commit messages.
Because of this and to help with (partly) automated generation of
change logs for each release we have defined a fixed syntax for commit
messages that is to be used.

##### `Never commit without a commit message explaining the commit!`

The syntax is as follows:

* Start with one of the following codes:

    ### `FEATURE:`
    A feature change.
    Most likely it will be an added feature, but it could also be removed.
    For additions there should be a corresponding ticket in the issue tracker.
    
    ### `BUGFIX #bug id:`
    A fix for a bug.
    There should be a ticket corresponding to this in the issue tracker as well as a new) unit test for the fix.
    
    ### `DOC:`
    A documentation related change.
    
    ### `TASK:`
    Anything not covered by the above categories, e.g. coding style cleanup or documentation changes.
    Usually only used if there's no corresponding ticket.

The code is followed by a short summary in the same line, **no full stop at the end.**
If the change is likely to break things on the user side, start the line with `[!!!]`.
This indicates a breaking change that needs human action when updating.
Make sure to explain why a change is breaking and in what circumstances.
Then follows (after a blank line) a custom message explaining what was done.
It should be written in a style that serves well for a change log read by users.
If there is more to say about a change add a new paragraph with background information below.
In case of breaking changes give a hint on what needs to be changed by the user.

### Examples of good and bad subject lines:

```sh

Introduce xyz service                   //BAD, missing code prefix
FEATURE: Introduce xyz service          // GOOD, valid code prefix
BUGFIX Fixed bug xyz                    // BAD, subject should be written in present tense, no defect id
BUGFIX #2343: Remove expired sessions   // GOOD, the line explains what the change does, not what the
                                        bug is about (this should be explained in the following lines
                                        and in the related bug tracker ticket)
FEATURE: !!! A breaking change          // BAD, subject has to start with !!! for breaking changes
!!! FEATURE: Timed sections             // GOOD, subject starts with !!! for breaking changes
```
