// Mouse-only Parallax Effect
class ParallaxController {
  constructor() {
    this.layers = document.querySelectorAll('.parallax-layer');
    this.container = document.querySelector('.parallax-container');
    
    console.log('Parallax layers found:', this.layers.length);
    console.log('Container found:', this.container);
    
    // Configuration for each layer (mouse movement only) - increased sensitivity
    this.layerConfig = {
      'layer-1': { mouseSpeed: 0.15 },
      'layer-2': { mouseSpeed: 0.20 },
      'layer-3': { mouseSpeed: 0.25 },
      'layer-4': { mouseSpeed: 0.30 },
      'layer-5': { mouseSpeed: 0.35 },
      'layer-6': { mouseSpeed: 0.40 },
      'layer-7': { mouseSpeed: 0.45 }
    };
    
    this.init();
  }
  
  init() {
    this.setupEventListeners();
    this.animateLayersOnLoad();
  }
  
  animateLayersOnLoad() {
    // Start with all layers hidden and positioned below
    this.layers.forEach((layer, index) => {
      layer.style.opacity = '0';
      layer.style.transform = 'translateY(100px)';
      layer.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
    });
    
    // Animate layers from bottom to top (layer-7 to layer-1)
    // Reverse the order so layer-7 starts first
    const layerArray = Array.from(this.layers).reverse();
    
    layerArray.forEach((layer, index) => {
      setTimeout(() => {
        layer.style.opacity = '1';
        layer.style.transform = 'translateY(0)';
        console.log(`Layer ${layer.className} animated`);
      }, index * 200); // 200ms delay between each layer
    });
    
    // Remove transition after animation completes
    setTimeout(() => {
      this.layers.forEach(layer => {
        layer.style.transition = '';
      });
    }, (layerArray.length * 200) + 800);
  }
  
  setupEventListeners() {
    // Mouse parallax
    document.addEventListener('mousemove', (e) => this.handleMouseMove(e));
    
    // Subtle scroll parallax - only when scrolling within parallax area
    window.addEventListener('scroll', () => this.handleScroll());
  }
  
  handleScroll() {
    if (!this.container) return;
    
    const containerRect = this.container.getBoundingClientRect();
    const scrolled = window.pageYOffset;
    
    // Only apply scroll effect when parallax container is visible
    if (containerRect.bottom > 0 && containerRect.top < window.innerHeight) {
      this.layers.forEach(layer => {
        const layerClass = this.getLayerClass(layer);
        const config = this.layerConfig[layerClass];
        
        if (config) {
          // Very subtle scroll effect - much smaller than mouse effect
          const scrollSpeed = config.mouseSpeed * 0.1; // 10% of mouse speed
          const yOffset = scrolled * scrollSpeed;
          
          // Get current mouse transform and preserve it
          const currentTransform = layer.style.transform || '';
          const mouseTransform = this.extractMouseTransform(currentTransform);
          
          // Combine mouse and scroll transforms
          layer.style.transform = `${mouseTransform} translateY(${yOffset}px)`;
        }
      });
    }
  }
  
  extractMouseTransform(transform) {
    // Remove translateY from transform string, keep only translate()
    return transform.replace(/translateY\([^)]*\)/g, '').trim();
  }
  
  handleMouseMove(e) {
    if (!this.container) return;
    
    const rect = this.container.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;
    
    const deltaX = (e.clientX - centerX) / rect.width;
    const deltaY = (e.clientY - centerY) / rect.height;
    
    this.layers.forEach(layer => {
      const layerClass = this.getLayerClass(layer);
      const config = this.layerConfig[layerClass];
      
      if (config) {
        const moveX = deltaX * config.mouseSpeed * 100;
        const moveY = deltaY * config.mouseSpeed * 100;
        
        // Get current scroll transform and preserve it
        const currentTransform = layer.style.transform || '';
        const scrollTransform = this.extractScrollTransform(currentTransform);
        
        // Only apply transform if no loading animation is active
        if (!layer.style.transition) {
          // Combine scroll and mouse transforms
          layer.style.transform = `${scrollTransform} translate(${moveX}px, ${moveY}px)`;
        }
      }
    });
  }
  
  extractScrollTransform(transform) {
    // Extract only translateY from transform string
    const match = transform.match(/translateY\([^)]*\)/);
    return match ? match[0] : '';
  }
  
  getLayerClass(layer) {
    const classes = layer.className.split(' ');
    return classes.find(cls => cls.startsWith('layer-')) || 'layer-1';
  }
}

// Initialize parallax when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  console.log('DOM loaded, initializing mouse parallax...');
  const parallax = new ParallaxController();
  console.log('Mouse parallax initialized with', parallax.layers.length, 'layers');
});

// Price card scroll section
const secScroll = document.querySelector('.sec-scroll');
const cards = document.querySelectorAll('.pricecard');
let index = 0;
let scrolling = false;
let autoScrollInterval;

// Show initial card
showCard(0);

function showCard(i) {
  cards.forEach((card, j) => {
    if (j === i) {
      card.classList.add('active');
      card.classList.remove('hidden');
    } else {
      card.classList.remove('active');
      card.classList.add('hidden');
    }
  });
}

function nextCard() {
  if (index < cards.length - 1) {
    index++;
  } else {
    index = 0; // Loop back to first card
  }
  showCard(index);
}

function prevCard() {
  if (index > 0) {
    index--;
  } else {
    index = cards.length - 1; // Loop to last card
  }
  showCard(index);
}

// Auto-scroll every 3 seconds
function startAutoScroll() {
  autoScrollInterval = setInterval(() => {
    if (!scrolling) {
      nextCard();
    }
  }, 1500); // 3 seconds
}

// Stop auto-scroll when user manually interacts
function stopAutoScroll() {
  clearInterval(autoScrollInterval);
}

// Restart auto-scroll after user interaction
function restartAutoScroll() {
  stopAutoScroll();
  setTimeout(() => {
    startAutoScroll();
  }, 3000); // Wait 3 seconds before resuming auto-scroll
}

// Start auto-scroll on load
startAutoScroll();

// Wheel event ONLY on .sec-scroll, prevent page scroll
if (secScroll) {
  secScroll.addEventListener('wheel', (e) => {
    e.preventDefault(); // IMPORTANT: prevent page scroll!

    if (scrolling) return;
    scrolling = true;

    stopAutoScroll(); // Stop auto-scroll when user interacts

    if (e.deltaY > 0) {
      nextCard();
    } else {
      prevCard();
    }

    setTimeout(() => {
      scrolling = false;
      restartAutoScroll(); // Restart auto-scroll after user interaction
    }, 500); // Reduced from 1500ms to 500ms
  }, { passive: false }); // passive:false required to call preventDefault()
}

// Optional: Keyboard navigation on whole window
window.addEventListener('keydown', (e) => {
  if (scrolling) return;

  if (e.key === 'ArrowDown') {
    stopAutoScroll();
    nextCard();
    scrolling = true;
  } else if (e.key === 'ArrowUp') {
    stopAutoScroll();
    prevCard();
    scrolling = true;
  }

  if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
    setTimeout(() => {
      scrolling = false;
      restartAutoScroll();
    }, 500); // Reduced from 1500ms to 500ms
  }
});

