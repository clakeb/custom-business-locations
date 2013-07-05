# Let's get things started by requiring any compass plugins here...
# You can either place your Compass extensions in an "extension" directory
# in your theme's "library" folder.  Or you can use Compass plugins on your
# computer that you installed as a gem (or .zip file). The path can vary
# so I will leave this one up to you.
	# Examples:
	# require susy-grids
	# require sassy-buttons
	# require more...

# Then let's tell Compass where our project is located. In this case,
# let's set it to the root of our WordPress theme.
	# http_path = "/wp-content/themes/anchorstudios/"

# Next, we should tell Compass what kind of project stage we are in.
	# # Development
	# output_style = :expanded
	# environment = :development
	# line_comments = true

	# Production
	output_style = :compressed
	environment = :production
	line_comments = false

# Okay, now we point Compass to our project's designated directories.
	# Project Assets Locations
	css_dir = "library/css"
	fonts_dir = "library/fonts"
	images_dir = "library/images"
	javascripts_dir = "library/js"
	sass_dir = "library/scss"

# Compass has a helper function that should generate relative urls from the
# generated css to assets, or absolute urls using the http path for that asset type.
# Set this to "true" if working locally and "false" if publishing to a server.
	# Localhost
	relative_assets = true

	# Server
	# relative_assets = false

# Compass will automatically add cache busters to your images based on image timestamps.
# This will keep browser caches from displaying the wrong image if you change the image
# but not the url. If you don’t want this behavior, it's easy to configure or disable:
	# asset_cache_buster do |http_path, real_path|
	# 	nil
	# end

# Compass allows you to choose a preferred SASS syntax (:scss or :sass).
# If you are prone to using oldschool :sass output, then I would strongly encourage
# you to learn the new and updated SASS sytax (uses .scss file extension).
	preferred_syntax = :scss