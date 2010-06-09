<script type="text/javascript">
<!--

self.scrollTo(100000,100000)

xscroll = getxscroll(this)
yscroll = getyscroll(this)

ieborder = 25

self.scrollTo(0,0)
if (self.innerWidth) {
    self.innerWidth = xscroll + self.innerWidth
    self.innerHeight = yscroll + self.innerHeight
} else if (document.documentElement && document.documentElement.clientWidth) {
    Xcorrection = screen.availWidth - (window.screenLeft + document.documentElement.clientWidth + xscroll + ieborder)
    if (Xcorrection < 0) { 
	moveBy(Xcorrection, 0)
    }
    Ycorrection = screen.availHeight - (window.screenTop + document.documentElement.clientHeight + yscroll + ieborder)
    if (Ycorrection < 0) { 
	moveBy(0, Ycorrection)
    }
    resizeBy(xscroll,yscroll)
} else if (document.body) {
    Xcorrection = screen.availWidth - (window.screenLeft + document.body.clientWidth + xscroll + ieborder)
    if (Xcorrection < 0) { 
	moveBy(Xcorrection, 0)
    }
    Ycorrection = screen.availHeight - (window.screenTop + document.body.clientHeight + yscroll + ieborder)
    if (Ycorrection < 0) { 
	moveBy(0, Ycorrection)
    }
    resizeBy(xscroll,yscroll)
}

//-->
</script>
