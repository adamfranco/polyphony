#!/bin/sh

#  @package polyphony.docs
#  
#  @copyright Copyright &copy; 2005, Middlebury College
#  @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
#  
#  @version $Id: generate.sh,v 1.2 2005/04/07 17:07:26 adamfranco Exp $

xsltproc ../xslt/changelog-simplehtml.xsl changelog.xml | sed -e 's/<?xml.*?>//' > ../../changelog.html
xsltproc ../xslt/changelog-plaintext.xsl changelog.xml | sed -e 's/<?xml.*?>//' > ../../changelog.txt

