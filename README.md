## Synopsis

Easily generate api pages with Symphony CMS. 

[![Build Status](https://api.travis-ci.org/iwyg/apipage.png?branch=master)](https://travis-ci.org/iwyg/apipage)

## Dependencies

- php >= 5.3.6
- all other dependencies should already be met by Symphony CMS

## Usage

- Install as usual.
- set default format and format url parameter in `System/Preferences`
- set pagetype to `API` (do not set any pagetype other then API when using the `content type mappings` extension)

- set your template's output format to `xml`, e.g.: 

		<?xml version="1.0" encoding="UTF-8"?>
		<xsl:stylesheet version="1.0"
		    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
			<xsl:output method="xml" omit-xml-declaration="yes" encoding="UTF-8" indent="yes" />
			
			<xsl:template match="/">
				<response> <!-- do your transformations here --></response>
			</xsl:template>
		</xsl:stylesheet>
		
- do your data transformation as you would usually do.		

## URL Parameter

- format: specify output format
- callback: specify a callback name for jsonp requests

## FAQ

- **Q:** why no php 5.2?
- **A:** Brace yourself, php 5.5 is comming.
- **Q:** I can do all this using a xml to json stylesheet. So why using this extension?
- **A:** Sure you can. The downside of doing so is, that all these stylesheets are a bit restricted and perform expensive string operations. The extensions XMLtoJSON parser uses the php C extension SimpleXML, which is much faster. It's reliable and it's tested.  

## TODOS

- make XMLtoJSON parser exchangable
- add more output formats like yml, python, etc. 
