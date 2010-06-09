function getxscroll (whichwindow) {
  if (typeof( whichwindow.pageXOffset ) == 'number') {
    // netscape
    return(whichwindow.pageXOffset)
  } else if (document.body && 
	(whichwindow.document.body.scrollLeft)) {
    // DOM
    return(whichwindow.document.body.scrollLeft)
  } else if (whichwindow.document.documentElement && 
        (whichwindow.document.documentElement.scrollLeft)) {
    // IE
    return(whichwindow.document.documentElement.scrollLeft)
  } else {
    return(0)
  }
}

function getyscroll (whichwindow) {
  if (typeof(whichwindow.pageYOffset) == 'number') {
    // netscape
    return(whichwindow.pageYOffset)
  } else if (document.body && 
	(whichwindow.document.body.scrollTop)) {
    // DOM
    return(whichwindow.document.body.scrollTop)
  } else if (whichwindow.document.documentElement && 
        (whichwindow.document.documentElement.scrollTop)) {
    // IE
    return(whichwindow.document.documentElement.scrollTop)
  } else {
    return(0)
  }
}

