	(function($) {  
        $.fn.smoothTabs = function(fadeSpeed) {
            var $tabCurrent = 'tabCurrent';
            var $tabHidden = 'tabHidden';
            var $tabVisible = 'tabVisible';
            var $parentUl = $("ul", this);
            var $parentDiv = $("#content");//$parentUl).parent();
            var $first = true;
            var $preLi = null;
            var $preIndex = -1;
            this.each(function() {
                $("ul li:first", this).addClass($tabCurrent);
                $parentDiv.find("div").addClass($tabHidden);
			});

            $('ul li', this).click(function() {
            	var $clickedLi = $(this);
                var $clickedIndex = $('li', $parentUl).index(this);
				if ($preIndex == $clickedIndex)
					return;

                var $currentDiv = $('div', $parentDiv).get($clickedIndex);


                if ($($currentDiv).attr('class') == $tabCurrent)
                    return false;
                    

                $('li', $parentUl).removeClass($tabCurrent);
                $(this).addClass($tabCurrent);

				$clickedLi.addClass("curr_li");

                if ($first) {
					$first = false;
					$($currentDiv).slideDown(fadeSpeed).addClass($tabVisible).removeClass($tabHidden);
                } else {
	                
					$preLi.removeClass("curr_li");
					
					$('.'+$tabVisible, $parentDiv).slideUp(fadeSpeed, function() {
	                    $($currentDiv).slideDown(fadeSpeed).addClass($tabVisible).removeClass($tabHidden);
	                });
	                $('.'+$tabVisible, $parentDiv).removeClass($tabVisible).addClass($tabHidden);
                }
				$preLi = $clickedLi;
				$preIndex = $clickedIndex;
                
            });
            $('ul li', this).hover(
            	function(){ $(this).addClass("hover_li"); },
            	function(){ $(this).removeClass("hover_li"); }
            );
        };
	})(jQuery); 
