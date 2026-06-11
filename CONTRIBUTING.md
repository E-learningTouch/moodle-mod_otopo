# Contributing Guidelines

**Pull requests, bug reports, and all other forms of contribution are welcomed and highly encouraged!**

This guidelines aims to establish transparent expectations for all contributors in the project. Our goal is to improve the plugin collaboratively, fostering an inclusive environment where everyone can contribute and promote a positive experience for both [contributors](CONTRIBUTORS.md) and [maintainers](MAINTAINERS.md).

## Table of Contents

- [:bulb: Asking Questions](#bulb-asking-questions)
- [:bookmark: Opening an Issue](#bookmark-opening-an-issue)
- [:bug: Bug Reports](#bug-bug-reports)
- [:sparkles: Feature Requests](#sparkles-feature-requests)
- [:repeat_one: Pull Requests](#repeat_one-pull-requests)
  - [:computer: Code Guidelines](#computer-code-guidelines)
- [:clap: Credits](#clap-credits)

## :bulb: Asking Questions

GitHub and GitLab issues are great for reporting bugs and suggesting new features, but they might not be the best place for detailed project troubleshooting. For more specific help with your project, feel free to ask questions on the [official Moodle forums](https://moodle.org/course/view.php?id=5), where you'll find a supportive community ready to assist!

## :bookmark: Opening an Issue

Before opening an issue, please ensure that you are using the latest version of the project. If you are not up-to-date, try updating to see if this resolves your issue first. Follow the official [GitHub](https://help.github.com/en/github/managing-your-work-on-github/creating-an-issue)/[GitLab](https://docs.gitlab.com/ee/user/project/issues/create_issues.html) guide for more information and to learn more about issues :blush:

## :bug: Bug Reports

A great way to contribute to the project is to send a detailed issue when you encounter a problem. We always appreciate a well-written, thorough bug report.

As a developer, think about the kind of ticket you'd like to receive and provide that level of detail.

- **Review the documentation**: Before opening a new issue, review the provided documentation to make sure you haven't missed a step and that it's really a bug.
- **Do not open a duplicate issues**: Check existing ones first to see if your problem has already been reported. If you find a matching problem, add any additional information by commenting or react to it. This will help us prioritize common issues.
- **Make sure to fill out the issue template completely**: It contains all the information we need to resolve your issue efficiently. Be clear and detailed, including steps to reproduce, stack traces, moodle version, database type and version, and screenshots if relevant.
- **Remember to use [GitHub](https://github.github.com/gfm)/[GitLab](https://docs.gitlab.com/ee/user/markdown.html) Flavored Markdown**, especially for code blocks and stack traces by wrapping them in backticks (```), as this improves readability.

## :sparkles: Feature Requests

Feature requests are welcome! While we will review all submissions, we cannot guarantee that every request will be accepted. Your idea might be great, but it could also fall outside the project's scope. If accepted, we cannot commit to a specific timeline for implementation or release. However, you are encouraged to submit a pull request to assist with the implementation!

- **Do not open a duplicate feature request**: Please search for existing requests before submitting a new one. If you find a similar or identical request, comment on that issue instead.
- **Fill out the issue template**: The template contains all the information we need to start a productive discussion and respond effectively to your request. We're not against not using it, but if you do, be sure to include as much information as possible.
- **Be specific**: Wanting to help improve the plugin is great! But you should take the time to determine the ultimate goal of the feature you'd like to see. Explore different possibilities, relationships with existing features and existing solutions to determine what it should do.

## :repeat_one: Pull Requests

Before forking the repo and creating a pull request for significant changes, it's usually best to first open an issue to discuss your proposed changes or your approach to solving the problem. If there's an existing issue, you can also discuss your approach in its comments.

*<u>Note:</u> All contributions will be licensed under the project's license.*

Here are some guidelines to keep in mind:

- **Smaller is better**: Submit one pull request per bug fix or feature. Each pull request should address a single issue or feature. Avoid refactoring or reformatting unrelated code in the same pull request. It's better to submit many rather than one large one. Large pull requests can be time-consuming to review and may even be rejected.
- **Coordinate bigger changes**: For substantial or complex changes, start by opening an issue to discuss the strategy with the maintainers. This way, you can avoid doing extensive work that might not align with the project's needs.
- **Prioritize clarity over cleverness**: Write code that is clear and easy to understand. Code is usually written once but read many times. Ensure that the purpose and logic are evident to other developers. If something isn't immediately clear, add comments to explain it.
- **Resolve any merge conflicts**: Follow the official [GitHub](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/resolving-a-merge-conflict-on-github)/[GitLab](https://docs.gitlab.com/ee/user/project/merge_requests/conflicts.html) guide for more information and to learn more about merge conflicts.
- **Add documentation**: Provide documentation for your changes either through code comments, updates to existing guides or in your request and we will add it correctly afterwards.
- **Update the [CHANGELOG.md](CHANGELOG.md) file**: Add details of all improvements or bug fixes. Include the relevant issue number if applicable, and your GitHub username (e.g: `- Fixed crash in profile view. #1 @username`). Optionally, you can update the [CONTRIBUTORS.md](CONTRIBUTORS.md) file, although we'll check that you're in the right spot.
- **Use the default repo branch**: When submitting your pull request, make sure it's on the default branch. If it's on any other branch, we'll systematically reject it, not because what you've done is wrong, but simply to keep the plugin properly up to date.
- **Promptly address any CI failures**: If your pull request fails to build or pass tests, please push another commit to fix the issues.

To learn ***how to fork***, follow the official [GitHub](https://docs.github.com/fr/pull-requests/collaborating-with-pull-requests/working-with-forks/fork-a-repo)/[GitLab](https://docs.gitlab.com/ee/user/project/repository/forking_workflow.html) guide and to learn ***how to create a pull request*** follow this one [GitHub](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request-from-a-fork)/[GitLab](https://docs.gitlab.com/ee/user/project/merge_requests/creating_merge_requests.html).

### :computer: Code Guidelines

To keep the code clean, consistent, and easy to maintain, we follow [Moodle coding guidelines](https://moodledev.io/general/development/policies/codingstyle) and [PHP PSR-12 extended coding style](https://www.php-fig.org/psr/psr-12). Here’s what you need to know:

- **Stick to Moodle’s Coding Guidelines**: Please make sure your code match with Moodle’s coding standards. This includes following their rules for formatting, naming conventions, and code documentation.
- **Ensure Code Quality**: Your code should pass all necessary checks, including syntax, style, and functionality. We strongly suggest that you use the lastest [CodeChecker](https://moodle.org/plugins/local_codechecker) with all options selected.
- **Test Coverage**: When possible, add [unit tests](https://moodledev.io/general/development/process/testing) or UI tests for your changes. Follow existing patterns for writing tests to ensure consistency.

## :clap: Credits

Written by [E-learning Touch'](https://www.elearningtouch.com/) for [Nantes Université](https://english.univ-nantes.fr/).
