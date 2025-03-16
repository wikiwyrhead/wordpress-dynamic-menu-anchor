/**
 * Wiki Dynamic Heading Anchors JavaScript
 *
 * Adds IDs to headings and stores them for menu creation
 */

document.addEventListener("DOMContentLoaded", function () {
  // Get settings from WordPress
  var settings = window.wikiDynamicHeadingAnchorsSettings || {
    headingTags: ["h2"],
    debugMode: false,
  };

  // Make sure headingTags is always an array
  if (!Array.isArray(settings.headingTags)) {
    settings.headingTags = [settings.headingTags];
  }

  // Debug log function
  function debugLog() {
    if (settings.debugMode) {
      console.log("[WIKI-ANCHOR-DEBUG]", ...arguments);
    }
  }

  // Store processed headings for PHP to access
  window.wikiDynamicHeadingAnchors = {
    processedHeadings: [],
    getHeadings: function () {
      return this.processedHeadings;
    },
  };

  // Generate ID from text
  function generateIdFromText(text) {
    return text
      .toLowerCase()
      .replace(/\s+/g, "-") // Replace spaces with hyphens
      .replace(/[^\w-]/g, "") // Remove non-alphanumeric characters
      .replace(/-+/g, "-"); // Replace multiple hyphens with a single one
  }

  // Store processed heading
  function storeProcessedHeading(heading) {
    const tagName = heading.tagName.toLowerCase();
    const text = heading.textContent.trim();
    const id = heading.id;

    // Add to processed headings array if not already there
    if (
      id &&
      !window.wikiDynamicHeadingAnchors.processedHeadings.some(
        (h) => h.id === id
      )
    ) {
      window.wikiDynamicHeadingAnchors.processedHeadings.push({
        id: id,
        text: text,
        tag: tagName,
      });
    }
  }

  // Process headings to add IDs
  function processHeadings(headings) {
    headings.forEach(function (heading) {
      // Skip if heading already has an ID
      if (heading.id) {
        storeProcessedHeading(heading);
        return;
      }

      const text = heading.textContent.trim();
      if (text) {
        const anchorId = generateIdFromText(text);
        if (anchorId) {
          heading.id = anchorId; // Assign the ID to the heading
          debugLog("Added ID to heading:", text, anchorId);
          storeProcessedHeading(heading);
        }
      }
    });
  }

  // Function to add IDs to headings
  function addHeadingIds() {
    // Process each heading tag type
    settings.headingTags.forEach(function (tag) {
      // Find headings in content areas
      const contentAreas = document.querySelectorAll(
        ".entry-content, .post-content, article, main, .site-content, " +
          ".elementor-widget-container, .elementor-text-editor"
      );

      if (contentAreas.length === 0) {
        // If no content areas found, scan entire document
        const headings = document.querySelectorAll(tag);
        processHeadings(headings);
      } else {
        // Process headings in each content area
        contentAreas.forEach(function (container) {
          const headings = container.querySelectorAll(tag);
          processHeadings(headings);
        });
      }
    });
  }

  // Handle smooth scrolling to anchors
  function handleHashNavigation() {
    if (window.location.hash) {
      const hash = window.location.hash.substring(1);
      const targetElement = document.getElementById(hash);

      if (targetElement) {
        // Add a small delay to ensure the page is fully loaded
        setTimeout(function () {
          const scrollOffset = settings.scrollOffset || 100;
          const elementPosition = targetElement.getBoundingClientRect().top;
          const offsetPosition =
            elementPosition + window.pageYOffset - scrollOffset;

          window.scrollTo({
            top: offsetPosition,
            behavior: "smooth",
          });

          // Focus the element for accessibility
          targetElement.setAttribute("tabindex", "-1");
          targetElement.focus({ preventScroll: true });
        }, 100);
      }
    }
  }

  // Run initially
  addHeadingIds();

  // Handle hash navigation after IDs are added
  handleHashNavigation();

  // Watch for dynamic content changes
  if (window.MutationObserver) {
    const observer = new MutationObserver(function (mutations) {
      let shouldProcess = false;
      mutations.forEach(function (mutation) {
        if (mutation.addedNodes.length > 0) {
          shouldProcess = true;
        }
      });

      if (shouldProcess) {
        addHeadingIds();
        // Check for hash navigation after dynamic content is processed
        handleHashNavigation();
      }
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });
  } else {
    // Fallback for browsers without MutationObserver
    setInterval(addHeadingIds, 2000);
  }

  // Handle clicks on anchor links for smooth scrolling
  document.addEventListener("click", function (event) {
    // Check if the clicked element is an anchor link
    if (event.target.tagName === "A" && event.target.hash) {
      const hash = event.target.hash.substring(1);
      const targetElement = document.getElementById(hash);

      // Only handle links to elements on the current page
      if (
        targetElement &&
        (event.target.pathname === window.location.pathname ||
          event.target.href.split("#")[0] ===
            window.location.href.split("#")[0])
      ) {
        event.preventDefault();

        const scrollOffset = settings.scrollOffset || 100;
        const elementPosition = targetElement.getBoundingClientRect().top;
        const offsetPosition =
          elementPosition + window.pageYOffset - scrollOffset;

        window.scrollTo({
          top: offsetPosition,
          behavior: "smooth",
        });

        // Update URL hash without jumping
        history.pushState(null, null, event.target.href);

        // Focus the element for accessibility
        targetElement.setAttribute("tabindex", "-1");
        targetElement.focus({ preventScroll: true });
      }
    }
  });
});
