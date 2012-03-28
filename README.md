[Twine](http://www.nterms.com/tools/twine)
======================================================

**Twine** is a simple tool that makes it easy to generates an HTML documention by merging a folder based set of HTML partials with a template. 
This tool embeds HTML snippts in each file inside the directory structure into a pre defined template. Template is also an HTML file which may be a common layout for the whole documentation.
However this tool might not be suitable for large projects that might have more than 1000 files.

I have not tested all the cases where the code fails and really appriciate your help in finding issues if you are using the tool. If you find an issue please report them at the issue tracker.  

### Features

- Templating
- Recursive directory processing
- Generates a unique identifire for each page
- Process titles
- Process meta tag contents (description)
- Generates breadcrumbs
- Single file distribution

### Goals

- Templating
- Recursive directory processing
- Generating uinque ID for each page
- Process page titles
- Process meta tag contents
- Generate breadcrumbs

### System requirements

**Twine** is a PHP script that should be hosted in a web server and accessed via web browser.

- PHP 5.1+
- Web browser (IE, FF, Chrome, Opera, Safari)

### Installation

1. Download the **twine.php**
2. Upload it to the docroot of your server (or any folder of your choice)

### Sample usage



### MIT License

Copyright (C) 2012 [nterms](http://nterms.com)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.