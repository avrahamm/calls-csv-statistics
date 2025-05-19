# Git History Cleanup

## What was done

The git history of this repository has been cleaned up to remove all old commits and start fresh from the "initial scaffolding" commit. This was done using the following steps:

1. Created a new orphan branch with no history: `git checkout --orphan temp_branch`
2. Added all files to the staging area: `git add .`
3. Created a new commit with the same message as the original "initial scaffolding" commit: `git commit -m "initial scaffolding"`
4. Deleted the old main branch: `git branch -D main`
5. Renamed the temp_branch to main: `git branch -m main`
6. Removed the existing remote repositories since they pointed to the Symfony skeleton repository:
   - `git remote remove origin`
   - `git remote remove composer`

## Current state

The repository now has a clean git history with only one commit: "initial scaffolding". All old commits have been removed.

## Instructions for team members

If you have already cloned this repository before the history cleanup, you need to update your local repository to match the new history. You can do this by:

1. Fetching the latest changes: `git fetch`
2. Resetting your local main branch to match the remote main branch: `git reset --hard origin/main`

If you haven't cloned the repository yet, you can simply clone it as usual and you'll get the clean history.

## Setting up a new remote repository

Since the old remote repositories have been removed, you need to set up a new remote repository for this project. You can do this by:

1. Creating a new repository on GitHub, GitLab, or any other git hosting service
2. Adding the new repository as a remote: `git remote add origin <repository-url>`
3. Pushing the main branch to the new remote: `git push -u origin main`