
#
# Controller route specifications
#

[content:/content]
/:word/:num = ( contentType, contentId )
[*]

[category:/category]
/:num = showCategory( contentId )
[*]

/ = category()
