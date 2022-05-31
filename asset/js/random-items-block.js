'use strict';

class RandomItemsBlock {

  constructor(itemIds, randIdx, basePath) {
    this.API_PATH = "api/items";
    this.itemIds = itemIds;
    this.randIdx = randIdx;
    this.basePath = basePath;
  }

  getNewRandomItem(el) {
    const self = this;
    this.randIdx = (this.randIdx + 1) % this.itemIds.length;
    $.ajax({
      url: self.basePath + "/" + self.API_PATH + "/" + self.itemIds[self.randIdx],
      targetEl: el,
      success: function(data) {
          self.parseApiData(data, this.targetEl);
        }
      }
    );
  }

  parseApiData(data, targetEl) {
    const itemImageUrl = typeof(data['thumbnail_display_urls']['large']) != 'undefined' ? data['thumbnail_display_urls']['large'] : null;
    const itemTitle = typeof(data['o:title']) != 'undefined' ? data['o:title'] : null;
    const itemDescription = typeof(data['dcterms:description'][0]['@value']) != 'undefined' ? data['dcterms:description'][0]['@value'] : null;
    if (itemImageUrl != null && itemTitle != null && itemDescription != null) {
      this.renderApiData(itemImageUrl, itemTitle, itemDescription, targetEl);
    }
    else this.getRandomItem(targetEl);
  }

  renderApiData(itemImageUrl, itemTitle, itemDescription, targetEl) {
    let html = '<img src="' + itemImageUrl + '">';
    html += '<h3>' + itemTitle + '</h3>';
    html += '<p>' + itemDescription + '</p>';
    targetEl.html(html);
  }
}

(function($) {
    $(document).ready(function() {
      $('.random-items-block-switch').click(function(e) {
        e.preventDefault();
        randomItemsBlock.getNewRandomItem($(this).siblings('.random-item'));
      })
    });
})(jQuery);
