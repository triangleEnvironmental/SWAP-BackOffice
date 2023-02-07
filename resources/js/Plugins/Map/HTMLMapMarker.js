export default function getHtmlMarkerClass() {
  class HTMLMapMarker extends google.maps.OverlayView {
    constructor(args) {
      super();
      this.position = args.position;
      this.html = args.html;
      this.setMap(args.map);
    }

    createDiv() {
      this.div = document.createElement('div');
      this.div.style.position = 'absolute';
      this.div.style['pointer-events'] = 'none';
      if (this.html) {
        this.div.innerHTML = this.html;
      }

      const self = this
      let clickTarget = this.div
      if (this.div.children.length > 0) {
        clickTarget = this.div.children[0]
      }
      clickTarget.style.cursor = 'pointer';
      clickTarget.style['pointer-events'] = 'all';
      clickTarget.addEventListener('click', event => {
        event.stopPropagation();
        google.maps.event.trigger(self, 'click');
      });
    }

    appendDivToOverlay() {
      const panes = this.getPanes();
      panes.overlayLayer.appendChild(this.div);
      panes.overlayMouseTarget.appendChild(this.div); // Make clickable
    }

    positionDiv() {
      const point = this.getProjection().fromLatLngToDivPixel(this.position);
      if (point) {
        this.div.style.left = `${point.x}px`;
        this.div.style.top = `${point.y}px`;
      }
    }

    draw() {
      if (!this.div) {
        this.createDiv();
        this.appendDivToOverlay();
      }
      this.positionDiv();
    }

    remove() {
      if (this.div) {
        this.div.parentNode.removeChild(this.div);
        this.div = null;
      }
    }

    getPosition() {
      return this.position;
    }

    getDraggable() {
      return false;
    }

    setAnimation(animation) {
      this.animation = animation
    }

    setAttribution(attribution) {
      this.attribution = attribution
    }

    setClickable(clickable) {
      this.clickable = clickable
    }

    setCursor(cursor) {
      this.cursor = cursor
    }

    setDraggable(draggable) {
      this.draggable = draggable
    }

    setHtml(html) {
      this.html = html
    }

    setLabel(label) {
      this.label = label
    }

    setOpacity(opacity) {
      this.opacity = opacity
    }

    setOptions(options) {
      this.options = options
    }

    setPlace(place) {
      this.place = place
    }

    setPosition(position) {
      this.position = position
    }

    setShape(shape) {
      this.shape = shape
    }

    setTitle(title) {
      this.title = title
    }

    setZIndex(zIndex) {
      this.zIndex = zIndex
    }

    setVisible(visible) {
      this.visible = visible
    }

  }

  return HTMLMapMarker
}
