'use strict';

class RandomItemsBlock {

  constructor(itemIds, randIdx, totalImages, basePath, siteSlug, linkToItems = false) {
    this.API_PATH = "api/items";
    this.itemIds = itemIds;
    this.randIdx = randIdx;
    this.basePath = basePath;
    this.siteSlug = siteSlug;
    this.totalImages = totalImages;
    this.linkToItems = linkToItems;
    this.itemPath = this.basePath + "/s/" + this.siteSlug + "/item/";
  }

  getNewRandomItems(el) {
    el.html('');
    for (let i = 1; i <= this.totalImages; i++) {
      this.randIdx = (this.randIdx + i) % this.itemIds.length;
      this.getRandomItem(el)
    }
  }

  getRandomItem(el) {
    const self = this;
    $.ajax({
      url: self.basePath + "/" + self.API_PATH + "/" + self.itemIds[self.randIdx],
      targetEl: el,
      success: function(data) {
          self.parseApiData(data, this.targetEl);
        }
      }
    );
  }

  // FIXME: Can we pass the description field in so we can look at something other than dcterms:description?
  parseApiData(data, targetEl) {
    const itemId = data['o:id'];
    const itemImageUrl = typeof(data['thumbnail_display_urls']['large']) != 'undefined' ? data['thumbnail_display_urls']['large'] : null;
    const itemTitle = typeof(data['o:title']) != 'undefined' ? data['o:title'] : null;
    const itemDescription = typeof(data['dcterms:description']) != 'undefined' ? data['dcterms:description'][0]['@value'] : null;
    if (itemImageUrl != null && itemTitle != null && itemDescription != null) {
      this.renderApiData(itemId, itemImageUrl, itemTitle, itemDescription, targetEl);
    }
    else {
      this.randIdx = (this.randIdx + 1) % this.itemIds.length;
      this.getRandomItem(targetEl);
    }
  }

  renderApiData(itemId, itemImageUrl, itemTitle, itemDescription, targetEl) {
    let html = '';
    if (this.linkToItems == 1) {
      html += '<a href="' + this.itemPath + itemId + '"><img src="' + itemImageUrl + '"></a>';
      html += '<h3><a href="' + this.itemPath + itemId + '">' + itemTitle + '</a></h3>';
    }
    else {
      html += '<img src="' + itemImageUrl + '">';
      html += '<h3>' + itemTitle + '</h3>';
    }
    html += '<p>' + itemDescription + '</p>';
    targetEl.append(html);
  }
}

(function($) {
    $(document).ready(function() {
      $('.random-items-block-switch').click(function(e) {
        e.preventDefault();
        randomItemsBlock.getNewRandomItems($(this).siblings('.random-item'));
      })
    });
})(jQuery);
