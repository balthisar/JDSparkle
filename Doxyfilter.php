#!/usr/bin/php
<?php
	/*
	 * ABOUT THIS UGLY FILTER
	 *
	 * Some aspects of Doxygen markup are ugly. This file acts as a pre-filter to uglify
	 * Doxgygen comments so that they will parse correclty in Doxygen, without requiring
	 * us to put ugly stuff in the actual source code.
	 *
	 * For example I never, ever want to use HTML in comments, but I almost always want
	 * to make it code. So rather than polluting with `\<a>` we can simply write `<a>`
	 * and this filter will escape it.
	 *
	 * Things this filter corrects:
	 *
	 *    - Multiline comment openers can be used as dividers. Use any character you want.

	 *    - Any &lt; preceded immediately by a tick (`) will be escaped.

	 *    - The most common indentation will be stripped so it doesn't confuse the markdown
	 *      parser, allowing you to indent your comments in multiline comments.
	 *
	 * WARNING: this is a quick and dirty hack. The code isn't pretty and doesn't consider
	 * all kinds of use cases. It does the trick I need it to do, though.
	 *
	 */

	array_shift($argv);
	$inputfile = [];
	/**
	 * On Windows STDIN is broken (waits for user input if no file
	 * specified, and there's no way to check for a tty, so we won't
	 * accept STDIN unless a - parameter is found.
	 */
	 if (in_array('-', $argv))
	 {
		while (!feof(STDIN))
		{
			$inputfile[] = stream_get_line(STDIN, 1000000, "\n");
		}
		array_pop($inputfile);
	 }
	 else
	 /**
	  * Use the parameter as a filename, and load the file.
	  */
	{
		if (!$argv)
		{
			printf("You must specify a filename as the first input parameter.\n");
			die(1);
		}

		$filename = $argv[0];
		if (!file_exists($filename))
		{
			printf("File '%s' not found.\n", $filename);
			die(1);
		}
		$inputfile = file($filename, FILE_IGNORE_NEW_LINES);
	}

	/**
	 * Search out eligible comment blocks.
	 */
	 $maxLineNumber = count($inputfile) - 1;
	 for ($i = 0; $i <= $maxLineNumber; $i++)
	 {
		$possibleFirstLine = $inputfile[$i];

		if (preg_match("/.*\/\*\*.*/", $possibleFirstLine)) // found the beginning of an intesting comment.
		{
			// Find bounds and remove comment decorations.
			for ($j = $i; $j <= $maxLineNumber; $j++)
			{
				$possibleLastLine = $inputfile[$j];
				if (preg_match("/.*\*\//",  $possibleLastLine)) // found the last line.
				{
					if ($i != $j)
					{
						$inputfile[$i] = preg_replace("/(.*\/\*\*)([^\ ].*)/", "$1", $inputfile[$i]);
						$inputfile[$j] = preg_replace("/.*(\*\/)$/", "$1", $inputfile[$j]);
					}
					break; //$j = $maxLineNumber;
				}
			}

			// Escape &lt; if following a tick.
			for ($k = $i; $k <= $j; $k++)
			{
				$inputfile[$k] = preg_replace("/`</", "`\<", $inputfile[$k]);
			}

			if ( $i != $j )
			{
				$indentSpaces = PHP_INT_MAX;
				$indentTabs = PHP_INT_MAX;
				// Find greatest common whitespace
				for ($k = $i+1; $k < $j; $k++)
				{
					$delta = strlen($inputfile[$k]) - strlen(ltrim($inputfile[$k], " "));
					$indentSpaces = min($delta, $indentSpaces);
					$delta = strlen($inputfile[$k]) - strlen(ltrim($inputfile[$k], "\t"));
					$indentTabs = min($delta, $indentTabs);
				}
				// Adjust common whitespace
				for ($k = $i+1; $k < $j; $k++)
				{
					$inputfile[$k] = substr($inputfile[$k], max($indentTabs, $indentSpaces));
				}
			}

			$i = $j;
		}
	 }

	//echo implode("\n", $inputfile);
	//fwrite(STDERR, implode("\n", $inputfile));
	fwrite(STDOUT, implode("\n", $inputfile));

?>
