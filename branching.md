# Branching

## Quick Legend

<table>
  <thead>
    <tr>
      <th>Instance</th>
      <th>Branch</th>
      <th>Description, Instructions, Notes</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Working</td>
      <td>master</td>
      <td>Accepts merges from Features/Issues, versiones and Refactors</td>
    </tr>
    <tr>
      <td>Features/Issues</td>
      <td>feature/*</td>
      <td>Always branch off HEAD of Working</td>
    </tr>
    <tr>
      <td>Releases</td>
      <td>version-*</td>
      <td>Always branch off Working</td>
    </tr>
    <tr>
      <td>Refactors</td>
      <td>refactor/*</td>
      <td>Always branch off HEAD of Working</td>
    </tr>
  </tbody>
</table>

## Main Branches

The main repository will always hold one evergreen branches:

* `master`

The main branch should be considered `origin/master` and will be the main branch where the source code of `HEAD` always reflects a state with the latest delivered development changes for the next release. As a developer, you will you be branching and merging from `master`.

Consider `origin/stable` to always represent the latest code deployed to production. During day to day development, the `stable` branch will not be interacted with.

When the source code in the `master` branch is stable and has been deployed, all of the changes will be merged into `stable` and tagged with a release number. *How this is done in detail will be discussed later.*

## Supporting Branches

Supporting branches are used to aid parallel development between team members, ease tracking of features, and to assist in quickly fixing live production problems. Unlike the main branches, these branches always have a limited life time, since they will be removed eventually.

The different types of branches we may use are:

* Feature branches
* Release branches
* Refactor branches

Each of these branches have a specific purpose and are bound to strict rules as to which branches may be their originating branch and which branches must be their merge targets. Each branch and its usage is explained below.

### Feature Branches

Feature branches are used when developing a new feature or enhancement which has the potential of a development lifespan longer than a single deployment. When starting development, the deployment in which this feature will be released may not be known. No matter when the feature branch will be finished, it will always be merged back into the master branch.

During the lifespan of the feature development, the lead should watch the `master` branch (network tool or branch tool in Bitbucket) to see if there have been commits since the feature was branched. Any and all changes to `master` should be merged into the feature before merging back to `master`; this can be done at various times during the project or at the end, but time to handle merge conflicts should be accounted for.

<unique> represents the feature name.

* Must branch from: `master`
* Must merge back into: `master`
* Branch naming convention: `feature/<unique>`

#### Working with a feature branch

If the branch does not exist yet (check with the Lead), create the branch locally and then push to Bitbucket. A feature branch should always be 'publicly' available. That is, development should never exist in just one developer's local branch.

```
$ git checkout -b feature-id master                 // creates a local branch for the new feature
$ git push origin feature-id                        // makes the new feature remotely available
```

Periodically, changes made to `master` (if any) should be merged back into your feature branch.

```
$ git merge master                                  // merges changes from master into feature branch
```

When development on the feature is complete, the lead (or engineer in charge) should merge changes into `master` and then make sure the remote branch is deleted.

```
$ git checkout master                               // change to the master branch  
$ git merge --no-ff feature/id                      // makes sure to create a commit object during merge
$ git push origin master                            // push merge changes
$ git push origin :feature/id                       // deletes the remote branch
```

#### Working with a refactor branch

If the branch does not exist yet (check with the Lead), create the branch locally and then push to Bitbucket. A refactor branch should always be 'publicly' available. That is, development should never exist in just one developer's local branch.

```
$ git checkout -b refactor-id master                 // creates a local branch for the new refactor
$ git push origin refactor-id                        // makes the new refactor remotely available
```

Periodically, changes made to `master` (if any) should be merged back into your refactor branch.

```
$ git merge master                                  // merges changes from master into refactor branch
```

When development on the refactor is complete, the lead (or engineer in charge) should merge changes into `master` and then make sure the remote branch is deleted.

```
$ git checkout master                               // change to the master branch  
$ git merge --no-ff refactor/id                      // makes sure to create a commit object during merge
$ git push origin master                            // push merge changes
$ git push origin :refactor/id                       // deletes the remote branch
```


### Version Branches

A version branch comes from the need to act immediately upon an undesired state of a live production version. Additionally, because of the urgency, a version is not required to be be pushed during a scheduled deployment. Due to these requirements, a version branch is always branched from a tagged `stable` branch. This is done for two reasons:

* Development on the `master` branch can continue while the version is being addressed.
* A tagged `stable` branch still represents what is in production. At the point in time where a version is needed, there could have been multiple commits to `master` which would then no longer represent production.

<unique> represents the bugzilla defect id.

* Must branch from: tagged `stable`
* Must merge back into: `master` and `stable`
* Branch naming convention: `version-<unique>`

#### Working with a version branch

If the branch does not exist yet (check with the Lead), create the branch locally and then push to Bitbucket. A version branch should always be 'publicly' available. That is, development should never exist in just one developer's local branch.

```
$ git checkout -b version-id stable                  // creates a local branch for the new version
$ git push origin version-id                         // makes the new version remotely available
```

When development on the version is complete, [the Lead] should merge changes into `stable` and then update the tag.

```
$ git checkout stable                               // change to the stable branch
$ git merge --no-ff version-id                       // forces creation of commit object during merge
$ git tag -a <tag>                                  // tags the fix
$ git push origin stable --tags                     // push tag changes
```

Merge changes into `master` so not to lose the version and then delete the remote version branch.

```
$ git checkout master                               // change to the master branch
$ git merge --no-ff version/id                       // forces creation of commit object during merge
$ git push origin master                            // push merge changes
$ git push origin :version/id                        // deletes the remote branch
```

