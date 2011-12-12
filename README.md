## Developer notes


* Please use ABSPATH and PLUGINDIR when referencing plugin files. This is because at least Pablo symbolically links the plugins folder from a separate wordpress install (for development purposes).

## Compressing and packaging plugins

There is a script you can use that will attempt to compress js/css and package a  plugin directory.  The script location is:

    wp-content/pm-tools/pm_build_plugin

The script takes a single argument of the plugin foldername, such as "program_scheduler". The plugin will look for the "js" and "css" folders inside the plugin folder. Then it will do the following:

1. For the "js" folder, it will	 first detect any php files and run them through the php command so we have only js code in those files.
2. It will then compress __all__ files in the js folders.
3. Then it will also compress __all__ files in the "css" folder.
4. Finally, it will create a zip file named the same as the plugin with the rev number attached to it. The zip file will contain a single folder named similarly.