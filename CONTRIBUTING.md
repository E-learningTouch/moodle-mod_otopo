# Contributing Guidelines

**Pull requests, bug reports, and all other forms of contribution are welcomed and highly encouraged!**

This guidelines aims to establish transparent expectations for all contributors in the project. Our goal is to improve the plugin collaboratively, fostering an inclusive environment where everyone can contribute and promote a positive experience for both contributors and maintainers.

## Table of Contents

- [:bulb: Asking Questions](#bulb-asking-questions)
- [:bookmark: Opening an Issue](#bookmark-opening-an-issue)
- [:bug: Bug Reports](#bug-bug-reports)
- [:sparkles: Feature Requests](#sparkles-feature-requests)
- [:repeat_one: Pull Requests](#repeat_one-pull-requests)
  - [:computer: Code Guidelines](#computer-code-guidelines)
- [:clap: Credits](#clap-credits)

## :bulb: Asking Questions

GitHub issues are great for reporting bugs and suggesting new features, but they might not be the best place for detailed project troubleshooting. For more specific help with your project, feel free to ask questions on the [official Moodle forums](https://moodle.org/course/view.php?id=5), where you'll find a supportive community ready to assist!

## :bookmark: Opening an Issue

Before [creating an issue](https://help.github.com/en/github/managing-your-work-on-github/creating-an-issue), please ensure that you are using the latest version of the project. If you are not up-to-date, try updating to see if this resolves your issue first.

## :bug: Bug Reports

A great way to contribute to the project is to send a detailed issue when you encounter a problem. We always appreciate a well-written, thorough bug report.

As a developer, think about the kind of ticket you'd like to receive and provide that level of detail.

- **Review the documentation** before opening a new issue.
- **Do not open a duplicate issues!** Check existing ones first to see if your problem has already been reported. If you find a matching issue, add any extra information you have by commenting otherwise **use reactions**. This will help us prioritize common issues.
- **Make sure to fill out the issue template completely**. It contains all the information we need to resolve your issue efficiently. Be clear and detailed, including steps to reproduce, stack traces, moodle version, database type and version, and screenshots if relevant.
- **Remember to use [GitHub-flavored Markdown](https://help.github.com/en/github/writing-on-github/basic-writing-and-formatting-syntax)**, especially for code blocks and stack traces by wrapping them in backticks (```), as this improves readability.

## :sparkles: Feature Requests

Feature requests are welcome! While we will review all submissions, we cannot guarantee that every request will be accepted. Your idea might be great, but it could also fall outside the project's scope. If accepted, we cannot commit to a specific timeline for implementation or release. However, you are encouraged to submit a pull request to assist with the implementation!

- **Do not open a duplicate feature request**. Please search for existing requests before submitting a new one. If you find a similar or identical request, comment on that issue instead.
- **Fully complete the provided issue template**. The feature request template contains all the information needed for us to start a productive discussion.
- Be specific about the expected outcome of the feature and its relation to existing features. If possible, include details on implementation.

## :repeat_one: Pull Requests

Before forking the repo and creating a pull request for significant changes, it's usually best to first open an issue to discuss your proposed changes or your approach to solving the problem. If there's an existing issue, you can also discuss your approach in its comments.

*<u>Note:</u> All contributions will be licensed under the project's license.*

Here are some guidelines to keep in mind:

- **Smaller is better**. Submit one pull request per bug fix or feature. Each pull request should address a single issue or feature. Avoid refactoring or reformatting unrelated code in the same pull request. It's better to submit many rather than one large one. Large pull requests can be time-consuming to review and may even be rejected.
- **Coordinate bigger changes**. For substantial or complex changes, start by opening an issue to discuss the strategy with the maintainers. This way, you can avoid doing extensive work that might not align with the project's needs.
- **Prioritize clarity over cleverness**. Write code that is clear and easy to understand. Code is usually written once but read many times. Ensure that the purpose and logic are evident to other developers. If something isn't immediately clear, add comments to explain it.
- **[Resolve any merge conflicts](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/resolving-a-merge-conflict-on-github)** that arise.
- **Add documentation**. Provide documentation for your changes either through code comments, updates to existing guides or in your request and we will add it correctly afterwards.
- **Update the [CHANGELOG.md](CHANGELOG.md) fiel** with details of all improvements or bug fixes. Include the relevant issue number if applicable, and your GitHub username (e.g: `- Fixed crash in profile view. #1 @username`). Optionally, you can update the [CONTRIBUTORS.md](CONTRIBUTORS.md) file, although we'll check that you're in the right spot.
- **Use the repo's default branch**. Fork the project and [submit your pull request](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request-from-a-fork) to the repo's default branch.
- **Promptly address any CI failures**. If your pull request fails to build or pass tests, please push another commit to fix the issues.

### :computer: Code Guidelines

To keep the code clean, consistent, and easy to maintain, we follow [Moodle coding guidelines](https://moodledev.io/general/development/policies/codingstyle) and [PHP PSR-12 extended coding style](https://www.php-fig.org/psr/psr-12). Here’s what you need to know:

- **Stick to Moodle’s Coding Guidelines**. Please make sure your code match with Moodle’s coding standards. This includes following their rules for formatting, naming conventions, and code documentation.
- **Ensure Code Quality**. Your code should pass all necessary checks, including syntax, style, and functionality. We strongly suggest that you use the lastest [CodeChecker](https://moodle.org/plugins/local_codechecker) with all options selected.
- **Test coverage**. When possible, add [unit tests](https://moodledev.io/general/development/process/testing) or UI tests for your changes. Follow existing patterns for writing tests to ensure consistency.

## :clap: Credits

Written by [E-learning Touch'](https://www.elearningtouch.com/) for [Nantes Université](https://english.univ-nantes.fr/).
