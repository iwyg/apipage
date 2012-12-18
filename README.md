## Synopsis

Generate api pages with symphony cms  

## Dependencies

- php >= 5.3.6
- all other dependencies should already be met be Symphony CMS

## Usage

- Install as usual.
- set default format and format url parameter in `System/Preferences`
- set pagetype to `API` (do not set any pagetype other then API when using the `content type mappings` extension)

- set your templates output format to `xml`, e.g.: 

		<?xml version="1.0" encoding="UTF-8"?>
		<xsl:stylesheet version="1.0"
		    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
			<xsl:output method="xml" omit-xml-declaration="yes" encoding="UTF-8" indent="yes" />
			
			<xsl:template match="/">
				<response> <!-- do your transformations here --></response>
			</xsl:template>
		</xsl:stylesheet>

## TODOS

- make XML to JSON parser exchangable
- add more output formats like yml, python, etc. 






