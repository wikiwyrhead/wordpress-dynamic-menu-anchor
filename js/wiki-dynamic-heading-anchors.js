/**
 * Wiki Dynamic Heading Anchors JavaScript
 * 
 * Adds IDs to headings
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get settings from WordPress
    var settings = window.wikiDynamicHeadingAnchorsSettings || {
        headingTags: ['h2'],
        menuClass: 'heading-anchor',
        debugMode: false
    };
    
    // Make sure headingTags is always an array
    if (!Array.isArray(settings.headingTags)) {
        settings.headingTags = [settings.headingTags];
    }
    
    // Debug log function
    function debugLog() {
        if (settings.debugMode) {
            console.log('[WIKI-ANCHOR-DEBUG]', ...arguments);
        }
    }
    
    // Debug log settings
    debugLog('Plugin initialized with settings:', settings);
    
    // Check if we have heading data from PHP
    if (window.wikiDynamicHeadingAnchorsData) {
        debugLog('PHP detected headings:', window.wikiDynamicHeadingAnchorsData.headings);
        debugLog('Current post ID:', window.wikiDynamicHeadingAnchorsData.postId);
        debugLog('Post type:', window.wikiDynamicHeadingAnchorsData.postType);
        debugLog('Post content preview:', window.wikiDynamicHeadingAnchorsData.postContent);
    } else {
        debugLog('No PHP heading data available');
    }
    
    // Store processed headings for external access
    window.wikiDynamicHeadingAnchors = {
        processedHeadings: [],
        getHeadings: function() {
            return this.processedHeadings;
        },
        debugInfo: {
            contentContainers: [],
            scannedElements: 0,
            foundHeadings: 0,
            elementorWidgets: []
        }
    };
    
    // Function to add IDs to headings
    function addHeadingIds() {
        debugLog('Starting heading detection');
        
        // Process each heading tag type
        settings.headingTags.forEach(function(tag) {
            debugLog('Scanning for tag:', tag);
            
            // Find all content containers - try common content container selectors
            const contentContainers = document.querySelectorAll(
                '.entry-content, .elementor-widget-container, article, .post-content, ' +
                '.site-content, .content-area, main, .page-content, .post, ' +
                '.elementor-text-editor, .fl-module-content, .et_pb_text_inner, ' +
                '.content, .page, .single, .single-post, .single-page, ' +
                '.post-entry, .entry, .blog-entry, .blog-post, ' +
                // Add more Elementor-specific selectors
                '.elementor-widget-heading, .elementor-heading-title, ' +
                '.elementor-widget-theme-post-title, .elementor-widget-dce-dynamicposts-v2, ' +
                '.dynamic-content-for-elementor, .dce-post-title, .dce-item, ' +
                // Specific for Dynamic Content for Elementor
                '[data-widget-type="dyncontel-acf"], [data-element-type="widget"]'
            );
            
            // Log content containers found
            debugLog('Found ' + contentContainers.length + ' potential content containers');
            window.wikiDynamicHeadingAnchors.debugInfo.contentContainers = Array.from(contentContainers).map(el => {
                return {
                    tagName: el.tagName,
                    classes: el.className,
                    id: el.id,
                    childrenCount: el.children.length
                };
            });
            
            // If no containers found, try the whole document
            if (contentContainers.length === 0) {
                debugLog('No content containers found, scanning entire document');
                // Query all heading elements of the current tag that don't have IDs
                const headings = document.querySelectorAll(tag);
                debugLog('Found ' + headings.length + ' ' + tag + ' elements in document');
                processHeadings(headings);
            } else {
                // Process headings in each container
                contentContainers.forEach(function(container, index) {
                    debugLog('Scanning container #' + index + ':', container.tagName, container.className);
                    const headings = container.querySelectorAll(tag);
                    debugLog('Found ' + headings.length + ' ' + tag + ' elements in container #' + index);
                    processHeadings(headings);
                });
            }
        });
        
        // Also look for elements with heading-like classes or data attributes
        debugLog('Scanning for elements with heading-like classes or attributes');
        const dynamicHeadings = document.querySelectorAll(
            '.heading, .title, .section-title, .entry-title, ' +
            '[class*="heading"], [class*="title"], ' +
            '[data-element-type="heading"], [data-widget-type*="heading"], ' +
            '[data-widget-type*="title"], .dce-title, .dce-heading'
        );
        
        debugLog('Found ' + dynamicHeadings.length + ' elements with heading-like attributes');
        processHeadings(dynamicHeadings);
        
        // Look specifically for Elementor widgets
        const elementorWidgets = document.querySelectorAll('.elementor-widget');
        debugLog('Found ' + elementorWidgets.length + ' Elementor widgets');
        
        // Store Elementor widget info for debugging
        window.wikiDynamicHeadingAnchors.debugInfo.elementorWidgets = Array.from(elementorWidgets).map(el => {
            return {
                id: el.id,
                classes: el.className,
                dataWidgetType: el.getAttribute('data-widget_type'),
                innerHTML: el.innerHTML.substring(0, 100) + '...'
            };
        });
        
        // Final debug summary
        debugLog('Heading detection complete. Found ' + window.wikiDynamicHeadingAnchors.processedHeadings.length + ' headings');
        debugLog('Processed headings:', window.wikiDynamicHeadingAnchors.processedHeadings);
    }
    
    // Process headings to add IDs
    function processHeadings(headings) {
        window.wikiDynamicHeadingAnchors.debugInfo.scannedElements += headings.length;
        
        headings.forEach(function(heading) {
            // Skip if heading already has an ID
            if (heading.id) {
                debugLog('Heading already has ID:', heading.id, heading.textContent.trim());
                // Store already processed heading
                storeProcessedHeading(heading);
                return;
            }
            
            const text = heading.textContent.trim();
            if (text) {
                const anchorId = generateIdFromText(text);
                
                if (anchorId) {
                    heading.id = anchorId; // Assign the ID to the heading
                    debugLog('Added ID to heading:', text, anchorId);
                    window.wikiDynamicHeadingAnchors.debugInfo.foundHeadings++;
                    
                    // Store processed heading
                    storeProcessedHeading(heading);
                }
            }
        });
    }
    
    // Generate ID from text (extracted to be reusable)
    function generateIdFromText(text) {
        return text.toLowerCase()
            .replace(/\s+/g, '-') // Replace spaces with hyphens
            .replace(/[^\w-]/g, '') // Remove non-alphanumeric characters
            .replace(/-+/g, '-'); // Replace multiple hyphens with a single one
    }
    
    // Store processed heading for external access
    function storeProcessedHeading(heading) {
        const tagName = heading.tagName.toLowerCase();
        const text = heading.textContent.trim();
        const id = heading.id;
        
        // Add to processed headings array if not already there
        if (id && !window.wikiDynamicHeadingAnchors.processedHeadings.some(h => h.id === id)) {
            window.wikiDynamicHeadingAnchors.processedHeadings.push({
                id: id,
                text: text,
                tag: tagName,
                element: heading
            });
        }
    }
    
    // Add permalink functionality
    function addPermalinkFunctionality() {
        // Check if URL has a hash
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const targetElement = document.getElementById(hash);
            
            if (targetElement) {
                debugLog('Found hash target:', hash);
                // Scroll to the element with offset (if defined in settings)
                setTimeout(function() {
                    const offset = settings.scrollOffset || 0;
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;
                    
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }, 300);
            }
        }
        
        // Add click event for anchor links
        document.querySelectorAll('a[href*="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                const hash = href.substring(href.indexOf('#') + 1);
                
                if (hash) {
                    const targetElement = document.getElementById(hash);
                    
                    if (targetElement) {
                        e.preventDefault();
                        
                        const offset = settings.scrollOffset || 0;
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - offset;
                        
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                        
                        // Update URL without reloading the page
                        if (history.pushState) {
                            history.pushState(null, null, '#' + hash);
                        } else {
                            location.hash = '#' + hash;
                        }
                    }
                }
            });
        });
    }
    
    // Run initially
    addHeadingIds();
    addPermalinkFunctionality();
    
    // Watch for dynamic content changes
    if (window.MutationObserver) {
        const observer = new MutationObserver(function(mutations) {
            let shouldProcess = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    shouldProcess = true;
                }
            });
            
            if (shouldProcess) {
                debugLog('Detected DOM changes, rescanning for headings');
                addHeadingIds();
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        debugLog('MutationObserver initialized to watch for dynamic content');
    }
    
    // Expose debug info to console
    window.wikiAnchorDebug = function() {
        console.log('=== WIKI ANCHOR DEBUG INFO ===');
        console.log('Settings:', settings);
        console.log('Processed headings:', window.wikiDynamicHeadingAnchors.processedHeadings);
        console.log('Debug info:', window.wikiDynamicHeadingAnchors.debugInfo);
        
        // Check if we have PHP data
        if (window.wikiDynamicHeadingAnchorsData) {
            console.log('PHP data:', window.wikiDynamicHeadingAnchorsData);
            
            // Compare PHP headings with JavaScript headings
            console.log('Heading detection comparison:');
            console.log('  - PHP detected headings:', window.wikiDynamicHeadingAnchorsData.headings.length);
            console.log('  - JavaScript detected headings:', window.wikiDynamicHeadingAnchors.processedHeadings.length);
            
            // Check post type support
            if (window.wikiDynamicHeadingAnchorsData.debug) {
                const postType = window.wikiDynamicHeadingAnchorsData.debug.post_type;
                const supportedTypes = window.wikiDynamicHeadingAnchorsData.debug.post_types;
                console.log('Post type check:');
                console.log('  - Current post type:', postType);
                console.log('  - Supported post types:', supportedTypes);
                console.log('  - Is supported:', supportedTypes.includes(postType));
                
                // Content analysis
                console.log('Content analysis:');
                console.log('  - Content length:', window.wikiDynamicHeadingAnchorsData.debug.content_length);
                console.log('  - Content preview:', window.wikiDynamicHeadingAnchorsData.debug.content_preview);
                
                // Heading tags analysis
                console.log('Heading tags:');
                console.log('  - Configured heading tags:', window.wikiDynamicHeadingAnchorsData.debug.heading_tags);
                
                // Analyze DOM for heading tags
                const headingAnalysis = {};
                window.wikiDynamicHeadingAnchorsData.debug.heading_tags.forEach(tag => {
                    const allHeadings = document.querySelectorAll(tag);
                    const headingsWithIds = document.querySelectorAll(tag + '[id]');
                    headingAnalysis[tag] = {
                        total: allHeadings.length,
                        withIds: headingsWithIds.length,
                        withoutIds: allHeadings.length - headingsWithIds.length,
                        elements: Array.from(allHeadings).map(h => ({
                            text: h.textContent.trim(),
                            hasId: !!h.id,
                            id: h.id,
                            html: h.outerHTML.substring(0, 100) + (h.outerHTML.length > 100 ? '...' : '')
                        }))
                    };
                });
                console.log('DOM heading analysis:', headingAnalysis);
            }
        } else {
            console.log('No PHP data available');
        }
        
        // Analyze content containers
        console.log('Content container analysis:');
        const contentContainers = document.querySelectorAll(
            '.entry-content, .elementor-widget-container, article, .post-content, ' +
            '.site-content, .content-area, main, .page-content, .post, ' +
            '.elementor-text-editor, .fl-module-content, .et_pb_text_inner, ' +
            '.content, .page, .single, .single-post, .single-page, ' +
            '.post-entry, .entry, .blog-entry, .blog-post'
        );
        console.log('  - Found', contentContainers.length, 'potential content containers');
        
        // Return all debug data
        return {
            settings: settings,
            processedHeadings: window.wikiDynamicHeadingAnchors.processedHeadings,
            debugInfo: window.wikiDynamicHeadingAnchors.debugInfo,
            phpData: window.wikiDynamicHeadingAnchorsData,
            domAnalysis: {
                contentContainers: contentContainers.length,
                headings: headingAnalysis
            }
        };
    };
    
    // Log instructions for debugging
    console.log('=== WIKI ANCHOR DEBUG HELP ===');
    console.log('To see detailed debug info, run this in the console: window.wikiAnchorDebug()');
});
