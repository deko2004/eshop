// Shop filtering functionality

// Global variables
let filteredProducts = [];
let currentCategory = "all";
let currentSort = "default";
let currentSearch = "";

// Initialize the shop page
document.addEventListener("DOMContentLoaded", function () {
  // Wait a bit to ensure other scripts have run
  setTimeout(function () {
    console.log("Initializing shop filters...");

    // Initialize with all products
    filteredProducts = [...products];

    // Make sure currentCategory is set to "all" initially
    currentCategory = "all";

    // Make sure the category filter is set to "all"
    const categoryFilter = document.getElementById("categoryFilter");
    if (categoryFilter) {
      categoryFilter.value = "all";

      // Add animation class to category filter
      categoryFilter.classList.add("filter-initialized");

      // Add count badges to category options
      updateCategoryCountBadges();
    }

    // Set up event listeners for filters
    setupFilterListeners();

    // Add URL parameter handling for direct links
    handleURLParameters();

    // Don't load products immediately, wait for user to apply filters
    // This prevents conflict with existing product cards
    console.log(
      "Shop filters initialized with " + filteredProducts.length + " products"
    );

    // Log the current state of the products array
    console.log(
      "Products array:",
      products.map((p) => `${p.id}: ${p.name} (${p.category})`)
    );

    // Add clear search button
    addClearSearchButton();

    // Add keyboard shortcuts
    setupKeyboardShortcuts();
  }, 500);
});

// Set up event listeners for filter controls
function setupFilterListeners() {
  // Category filter
  const categoryFilter = document.getElementById("categoryFilter");
  if (categoryFilter) {
    // Make sure it's set to "all" initially
    categoryFilter.value = "all";

    categoryFilter.addEventListener("change", function () {
      currentCategory = this.value;
      console.log("Category changed to:", currentCategory);
      applyFilters();
    });
  }

  // Sort filter
  const sortFilter = document.getElementById("priceSort");
  if (sortFilter) {
    sortFilter.addEventListener("change", function () {
      currentSort = this.value;
      applyFilters();
    });
  }

  // Search input
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    // Create a better debounce function for search
    let debounceTimer;
    let lastSearchTerm = "";

    // Add placeholder with search tips
    searchInput.placeholder =
      "Search products (e.g. headphones, gaming, wireless...)";

    // Add autocomplete attribute for better browser support
    searchInput.setAttribute("autocomplete", "off");

    // Add aria attributes for accessibility
    searchInput.setAttribute("aria-label", "Search products");

    // Update search indicator
    const updateSearchIndicator = (isSearching) => {
      const searchButton = document.getElementById("searchButton");
      if (searchButton) {
        if (isSearching) {
          searchButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
          searchButton.setAttribute("aria-label", "Searching...");
        } else {
          searchButton.innerHTML = '<i class="fas fa-search"></i>';
          searchButton.setAttribute("aria-label", "Search");
        }
      }
    };

    // Create search suggestions container
    const suggestionsContainer = document.createElement("div");
    suggestionsContainer.className =
      "absolute z-50 bg-gray-700 w-full rounded-b-lg shadow-lg hidden";
    suggestionsContainer.style.top = "100%";
    suggestionsContainer.style.left = "0";
    suggestionsContainer.id = "searchSuggestions";
    searchInput.parentNode.style.position = "relative";
    searchInput.parentNode.appendChild(suggestionsContainer);

    // Function to update search suggestions
    const updateSuggestions = (searchTerm) => {
      if (!searchTerm || searchTerm.length < 2) {
        suggestionsContainer.classList.add("hidden");
        return;
      }

      // Get all product names from DOM
      const productNames = [];
      document.querySelectorAll(".product-card h3").forEach((el) => {
        productNames.push(el.textContent.trim());
      });

      // Get all categories
      const categories = [];
      document.querySelectorAll(".product-card").forEach((card) => {
        const category = card.getAttribute("data-category");
        if (category && !categories.includes(category)) {
          categories.push(category);
        }
      });

      // Filter suggestions based on search term
      const term = searchTerm.toLowerCase();
      const matchingProducts = productNames
        .filter((name) => name.toLowerCase().includes(term))
        .slice(0, 3); // Limit to 3 suggestions

      const matchingCategories = categories.filter((cat) =>
        cat.toLowerCase().includes(term)
      );

      // Additional common search terms
      const commonTerms = [
        "wireless",
        "gaming",
        "rgb",
        "mechanical",
        "noise cancelling",
        "bluetooth",
      ];
      const matchingCommonTerms = commonTerms
        .filter((term) => term.includes(searchTerm.toLowerCase()))
        .slice(0, 2);

      // Build suggestions HTML
      if (
        matchingProducts.length ||
        matchingCategories.length ||
        matchingCommonTerms.length
      ) {
        let html = "";

        if (matchingProducts.length) {
          html += '<div class="p-2 text-gray-400 text-xs">Products</div>';
          matchingProducts.forEach((product) => {
            html += `<div class="p-2 text-white hover:bg-gray-600 cursor-pointer search-suggestion" data-value="${product}">${product}</div>`;
          });
        }

        if (matchingCategories.length) {
          html += '<div class="p-2 text-gray-400 text-xs">Categories</div>';
          matchingCategories.forEach((category) => {
            const displayCategory =
              category.charAt(0).toUpperCase() + category.slice(1);
            html += `<div class="p-2 text-white hover:bg-gray-600 cursor-pointer search-suggestion" data-value="${category}">${displayCategory}</div>`;
          });
        }

        if (matchingCommonTerms.length) {
          html +=
            '<div class="p-2 text-gray-400 text-xs">Popular Searches</div>';
          matchingCommonTerms.forEach((term) => {
            html += `<div class="p-2 text-white hover:bg-gray-600 cursor-pointer search-suggestion" data-value="${term}">${term}</div>`;
          });
        }

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.classList.remove("hidden");

        // Add click event to suggestions
        document.querySelectorAll(".search-suggestion").forEach((el) => {
          el.addEventListener("click", function () {
            const value = this.getAttribute("data-value");
            searchInput.value = value;
            lastSearchTerm = value;
            currentSearch = value;
            applyFilters();
            suggestionsContainer.classList.add("hidden");
          });
        });
      } else {
        suggestionsContainer.classList.add("hidden");
      }
    };

    // Search on input change (with improved debounce)
    searchInput.addEventListener("input", function () {
      const searchTerm = this.value.trim();

      // Update suggestions immediately
      updateSuggestions(searchTerm);

      // Don't search if the term is the same
      if (searchTerm === lastSearchTerm) return;

      // Show searching indicator
      updateSearchIndicator(true);

      // Clear previous timer
      clearTimeout(debounceTimer);

      // Set new timer
      debounceTimer = setTimeout(() => {
        lastSearchTerm = searchTerm;
        currentSearch = searchTerm;
        applyFilters();

        // Hide searching indicator
        updateSearchIndicator(false);
      }, 400); // Slightly longer delay for better UX
    });

    // Search on enter key (immediate)
    searchInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        // Clear any pending debounce
        clearTimeout(debounceTimer);

        // Hide suggestions
        suggestionsContainer.classList.add("hidden");

        const searchTerm = this.value.trim();
        if (searchTerm === lastSearchTerm) return;

        lastSearchTerm = searchTerm;
        currentSearch = searchTerm;
        applyFilters();
      }
    });

    // Hide suggestions when clicking outside
    document.addEventListener("click", function (e) {
      if (
        !searchInput.contains(e.target) &&
        !suggestionsContainer.contains(e.target)
      ) {
        suggestionsContainer.classList.add("hidden");
      }
    });

    // Handle keyboard navigation in suggestions
    searchInput.addEventListener("keydown", function (e) {
      if (suggestionsContainer.classList.contains("hidden")) return;

      const suggestions =
        suggestionsContainer.querySelectorAll(".search-suggestion");
      if (!suggestions.length) return;

      let activeIndex = -1;
      suggestions.forEach((el, i) => {
        if (el.classList.contains("bg-gray-600")) {
          activeIndex = i;
        }
      });

      // Down arrow
      if (e.key === "ArrowDown") {
        e.preventDefault();
        activeIndex = (activeIndex + 1) % suggestions.length;
        suggestions.forEach((el, i) => {
          el.classList.toggle("bg-gray-600", i === activeIndex);
        });
      }

      // Up arrow
      if (e.key === "ArrowUp") {
        e.preventDefault();
        activeIndex =
          activeIndex <= 0 ? suggestions.length - 1 : activeIndex - 1;
        suggestions.forEach((el, i) => {
          el.classList.toggle("bg-gray-600", i === activeIndex);
        });
      }

      // Enter to select
      if (e.key === "Enter" && activeIndex >= 0) {
        e.preventDefault();
        const value = suggestions[activeIndex].getAttribute("data-value");
        searchInput.value = value;
        lastSearchTerm = value;
        currentSearch = value;
        applyFilters();
        suggestionsContainer.classList.add("hidden");
      }

      // Escape to close
      if (e.key === "Escape") {
        suggestionsContainer.classList.add("hidden");
      }
    });

    // Focus the search input when the page loads
    setTimeout(() => {
      searchInput.focus();
    }, 1000);
  }

  // Search button
  const searchButton = document.getElementById("searchButton");
  if (searchButton) {
    // Set initial icon
    searchButton.innerHTML = '<i class="fas fa-search"></i>';

    // Add click event
    searchButton.addEventListener("click", function () {
      const searchInput = document.getElementById("searchInput");
      if (!searchInput) return;

      const searchTerm = searchInput.value.trim();

      // If search input is empty and has focus, show all products
      if (searchTerm === "" && document.activeElement === searchInput) {
        currentSearch = "";
        applyFilters();
        return;
      }

      // If search input is empty, focus it
      if (searchTerm === "") {
        searchInput.focus();
        return;
      }

      // Otherwise, perform search
      currentSearch = searchTerm;
      applyFilters();

      // Show a brief animation on the button
      this.innerHTML = '<i class="fas fa-check"></i>';
      setTimeout(() => {
        this.innerHTML = '<i class="fas fa-search"></i>';
      }, 500);
    });
  }
}

// Apply all filters and update product display
function applyFilters() {
  console.log(
    "Applying filters - Category:",
    currentCategory,
    "Search:",
    currentSearch,
    "Sort:",
    currentSort
  );

  // Start with all products
  filteredProducts = [...products];
  console.log("Starting with", filteredProducts.length, "products");

  // Apply category filter
  if (currentCategory && currentCategory !== "all") {
    console.log("Filtering by category:", currentCategory);

    // Get all product cards to check their categories
    const productCards = document.querySelectorAll(".product-card");
    const categoryValues = Array.from(productCards).map((card) =>
      card.getAttribute("data-category")
    );
    console.log("Available categories in DOM:", [...new Set(categoryValues)]);

    filteredProducts = filteredProducts.filter((product) => {
      // Find the corresponding DOM element
      const productCard = document.querySelector(
        `.product-card[data-product-id="${product.id}"]`
      );
      const domCategory = productCard
        ? productCard.getAttribute("data-category")
        : "unknown";
      console.log(
        `Product ${product.id} (${product.name}): JS category=${product.category}, DOM category=${domCategory}`
      );

      // Use the category from the DOM element instead of the JS object
      return domCategory.toLowerCase() === currentCategory.toLowerCase();
    });

    console.log("After category filter:", filteredProducts.length, "products");
    console.log(
      "Remaining products:",
      filteredProducts.map((p) => p.id)
    );
  }

  // Apply search filter
  if (currentSearch && currentSearch.trim() !== "") {
    // Split search into keywords for better matching
    const searchTerms = currentSearch
      .toLowerCase()
      .split(/\s+/)
      .filter((term) => term.length > 0);
    console.log("Searching for keywords:", searchTerms);

    if (searchTerms.length > 0) {
      // First, get the actual product names from the DOM
      const productNameMap = {};
      document.querySelectorAll(".product-card").forEach((card) => {
        const productId = parseInt(card.getAttribute("data-product-id"));
        const nameElement = card.querySelector("h3");
        if (nameElement && productId) {
          productNameMap[productId] = nameElement.textContent.trim();
        }
      });

      console.log("Product names from DOM:", productNameMap);

      filteredProducts = filteredProducts.filter((product) => {
        // Get the actual product name from the DOM
        const actualProductName = productNameMap[product.id] || product.name;

        // Fields to search in
        const searchableFields = [
          product.name || "", // Name from JS
          actualProductName || "", // Name from DOM
          product.description || "", // Description
          product.category || "", // Category
          getProductSubtitle(product) || "", // Subtitle
        ];

        // Add product features if available
        if (product.features && Array.isArray(product.features)) {
          searchableFields.push(...product.features);
        }

        // Add product specifications if available
        if (product.specifications) {
          Object.values(product.specifications).forEach((spec) => {
            if (spec) searchableFields.push(spec.toString());
          });
        }

        // Convert all fields to lowercase for case-insensitive search
        const searchableText = searchableFields.join(" ").toLowerCase();

        // For debugging
        console.log(
          `Product ${product.id} searchable text: ${searchableText.substring(
            0,
            100
          )}...`
        );

        // Check if ANY search term is found in any of the fields (more lenient search)
        const anyTermMatch = searchTerms.some((term) =>
          searchableText.includes(term)
        );

        // For more precise search, require all terms to match
        const allTermsMatch = searchTerms.every((term) =>
          searchableText.includes(term)
        );

        // Use a more lenient approach - match if any term matches
        const isMatch = anyTermMatch;

        if (isMatch) {
          console.log(
            `Product ${product.id} (${actualProductName}) matches search terms`
          );
        }

        return isMatch;
      });
    }

    console.log("After search filter:", filteredProducts.length, "products");
    console.log(
      "Remaining products after search:",
      filteredProducts.map((p) => p.id)
    );
  }

  // Apply sorting
  if (currentSort && currentSort !== "default") {
    switch (currentSort) {
      case "price-asc":
        filteredProducts.sort((a, b) => a.price - b.price);
        console.log("Sorted by price: low to high");
        break;
      case "price-desc":
        filteredProducts.sort((a, b) => b.price - a.price);
        console.log("Sorted by price: high to low");
        break;
      case "newest":
        // Assuming newer products have higher IDs
        filteredProducts.sort((a, b) => b.id - a.id);
        console.log("Sorted by newest first");
        break;
      // Add more sorting options as needed
    }
  }

  // Update the product display
  loadProducts();

  // Show a message to the user
  if (filteredProducts.length === 0) {
    showAlert(
      "No products match your filters. Try different criteria.",
      "error"
    );
  } else if (filteredProducts.length < products.length) {
    showAlert(
      `Showing ${filteredProducts.length} filtered products`,
      "success"
    );
  }
}

// Load products into the grid using CSS only approach
function loadProducts() {
  // Get all product cards
  const productCards = document.querySelectorAll(".product-card");

  if (!productCards.length) return;

  // Get the no results message or create it if it doesn't exist
  let noResultsMessage = document.getElementById("noResultsMessage");
  if (!noResultsMessage) {
    noResultsMessage = document.createElement("div");
    noResultsMessage.id = "noResultsMessage";
    noResultsMessage.className = "hidden col-span-full text-center py-12";
    noResultsMessage.innerHTML = `
      <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
      <h3 class="text-xl font-bold text-gray-300 mb-2">No products found</h3>
      <p class="text-gray-400 mb-6">Try adjusting your filters or search terms</p>
      <button onclick="resetFilters()" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-lg transition-all duration-300">
          Clear All Filters
      </button>
    `;
    const productGrid = document.getElementById("productGrid");
    if (productGrid) {
      productGrid.appendChild(noResultsMessage);
    }
  }

  // Track how many products are visible
  let visibleCount = 0;

  // Loop through all product cards
  productCards.forEach((card) => {
    const productId = parseInt(card.getAttribute("data-product-id"));
    const category = card.getAttribute("data-category");

    // Find the corresponding product in the filtered products array
    const isVisible = filteredProducts.some(
      (product) => product.id === productId
    );

    if (isVisible) {
      // Show the product card
      card.classList.remove("hidden");
      visibleCount++;

      // Highlight search terms if there's a search query
      if (currentSearch && currentSearch.trim() !== "") {
        highlightSearchTerms(card, currentSearch);
      } else {
        // Remove any existing highlights
        removeHighlights(card);
      }
    } else {
      // Hide the product card
      card.classList.add("hidden");
    }
  });

  // Show or hide the no results message
  if (visibleCount === 0) {
    noResultsMessage.classList.remove("hidden");
  } else {
    noResultsMessage.classList.add("hidden");
  }

  console.log(`Showing ${visibleCount} products out of ${productCards.length}`);
}

// We no longer need the createProductCard function since we're using the existing cards

// Highlight search terms in product cards - now without yellow highlighting
function highlightSearchTerms(card, searchQuery) {
  if (!card || !searchQuery) return;

  // Get the elements that might contain text to highlight
  const titleElement = card.querySelector("h3");
  const subtitleElement = card.querySelector(".text-gray-400.text-sm");

  if (!titleElement && !subtitleElement) return;

  // Split search into terms
  const searchTerms = searchQuery
    .toLowerCase()
    .split(/\s+/)
    .filter((term) => term.length > 1); // Only highlight terms with at least 2 characters

  if (searchTerms.length === 0) return;

  // Instead of highlighting, we'll just store the original text
  // so we can restore it when needed

  // Store original title text if not already stored
  if (titleElement && !titleElement.dataset.originalText) {
    titleElement.dataset.originalText = titleElement.textContent;
  }

  // Store original subtitle text if not already stored
  if (subtitleElement && !subtitleElement.dataset.originalText) {
    subtitleElement.dataset.originalText = subtitleElement.textContent;
  }

  // We're not applying any visual highlighting now
  // Just adding a data attribute to mark that this card matches the search
  card.setAttribute("data-search-match", "true");
}

// Remove any search-related attributes from product cards
function removeHighlights(card) {
  if (!card) return;

  // Remove the search match attribute
  card.removeAttribute("data-search-match");

  // We're still keeping the original text restoration logic
  // in case we need to restore from any previous highlighting

  // Get the elements that might contain highlighted text
  const titleElement = card.querySelector("h3");
  const subtitleElement = card.querySelector(".text-gray-400.text-sm");

  // Restore original text in title
  if (titleElement && titleElement.dataset.originalText) {
    titleElement.textContent = titleElement.dataset.originalText;
  }

  // Restore original text in subtitle
  if (subtitleElement && subtitleElement.dataset.originalText) {
    subtitleElement.textContent = subtitleElement.dataset.originalText;
  }
}

// Helper function to escape special characters in regex
function escapeRegExp(string) {
  return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

// Update category count badges
function updateCategoryCountBadges() {
  const categoryFilter = document.getElementById("categoryFilter");
  if (!categoryFilter) return;

  // Get all product cards
  const productCards = document.querySelectorAll(".product-card");

  // Count products by category
  const categoryCounts = {
    all: productCards.length,
  };

  // Count products in each category
  productCards.forEach((card) => {
    const category = card.getAttribute("data-category");
    if (category) {
      if (!categoryCounts[category]) {
        categoryCounts[category] = 0;
      }
      categoryCounts[category]++;
    }
  });

  // Update option text with counts
  Array.from(categoryFilter.options).forEach((option) => {
    const category = option.value;
    const count = categoryCounts[category] || 0;

    // Add count badge to option text
    if (category === "all") {
      option.textContent = `All Categories (${count})`;
    } else {
      // Capitalize first letter of category
      const categoryName = category.charAt(0).toUpperCase() + category.slice(1);
      option.textContent = `${categoryName} (${count})`;
    }
  });
}

// Handle URL parameters for direct links
function handleURLParameters() {
  const urlParams = new URLSearchParams(window.location.search);

  // Check for category parameter
  const categoryParam = urlParams.get("category");
  if (categoryParam) {
    const categoryFilter = document.getElementById("categoryFilter");
    if (categoryFilter) {
      // Check if the category exists in the options
      const categoryExists = Array.from(categoryFilter.options).some(
        (option) => option.value.toLowerCase() === categoryParam.toLowerCase()
      );

      if (categoryExists) {
        categoryFilter.value = categoryParam.toLowerCase();
        currentCategory = categoryParam.toLowerCase();
        applyFilters();
      }
    }
  }

  // Check for search parameter
  const searchParam = urlParams.get("search");
  if (searchParam) {
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
      searchInput.value = searchParam;
      currentSearch = searchParam;
      applyFilters();
    }
  }

  // Check for sort parameter
  const sortParam = urlParams.get("sort");
  if (sortParam) {
    const sortFilter = document.getElementById("priceSort");
    if (sortFilter) {
      // Check if the sort option exists
      const sortExists = Array.from(sortFilter.options).some(
        (option) => option.value === sortParam
      );

      if (sortExists) {
        sortFilter.value = sortParam;
        currentSort = sortParam;
        applyFilters();
      }
    }
  }
}

// Add clear search button
function addClearSearchButton() {
  const searchInput = document.getElementById("searchInput");
  if (!searchInput) return;

  // Create clear button
  const clearButton = document.createElement("button");
  clearButton.type = "button";
  clearButton.className =
    "absolute right-10 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors duration-200";
  clearButton.innerHTML = '<i class="fas fa-times-circle"></i>';
  clearButton.style.display = "none"; // Hide initially

  // Add click event
  clearButton.addEventListener("click", function () {
    searchInput.value = "";
    currentSearch = "";
    applyFilters();
    this.style.display = "none";
    searchInput.focus();
  });

  // Add input event to show/hide clear button
  searchInput.addEventListener("input", function () {
    clearButton.style.display = this.value ? "block" : "none";
  });

  // Insert clear button
  searchInput.parentNode.style.position = "relative";
  searchInput.parentNode.appendChild(clearButton);
}

// Setup keyboard shortcuts
function setupKeyboardShortcuts() {
  document.addEventListener("keydown", function (e) {
    // Only apply shortcuts when not in an input field
    if (
      e.target.tagName === "INPUT" ||
      e.target.tagName === "TEXTAREA" ||
      e.target.tagName === "SELECT"
    ) {
      return;
    }

    // Ctrl+F or / to focus search
    if ((e.ctrlKey && e.key === "f") || e.key === "/") {
      e.preventDefault();
      const searchInput = document.getElementById("searchInput");
      if (searchInput) {
        searchInput.focus();
      }
    }

    // Escape to clear filters
    if (e.key === "Escape") {
      resetFilters();
    }
  });
}

// We no longer need the generateStars function since we're using the existing cards

// Get product subtitle based on product category - used for search
function getProductSubtitle(product) {
  if (!product || !product.category) return "";

  switch (product.category.toLowerCase()) {
    case "headphones":
      if (product.id === 1) return "Wireless Noise Cancelling";
      if (product.id === 2) return "True Wireless Water Resistant";
      if (product.id === 3) return "Professional Audio Wired";
      if (product.id === 7) return "7.1 Surround Sound RGB";
      break;
    case "keyboards":
      if (product.id === 4) return "RGB Backlit Blue Switches";
      if (product.id === 5) return "Customizable RGB Macro Keys";
      break;
    case "headphones":
      if (product.id === 1) return "Wireless Noise Cancelling";
      if (product.id === 2) return "True Wireless Water Resistant";
      if (product.id === 3) return "Professional Audio Wired";
      if (product.id === 6) return "Wireless Touch Controls";
      if (product.id === 7) return "7.1 Surround Sound RGB";
      break;
    case "laptops":
      return "RTX 4060 16GB RAM 1TB SSD";
  }
  return "";
}

// Reset all filters
function resetFilters() {
  // Reset global variables
  currentCategory = "all";
  currentSort = "default";
  currentSearch = "";

  // Reset UI elements
  const categoryFilter = document.getElementById("categoryFilter");
  if (categoryFilter) categoryFilter.value = "all";

  const sortFilter = document.getElementById("priceSort");
  if (sortFilter) sortFilter.value = "default";

  const searchInput = document.getElementById("searchInput");
  if (searchInput) searchInput.value = "";

  // Apply filters (which will now show all products)
  applyFilters();

  // Show message
  showAlert("Filters have been reset", "success");
}

// Show alert message (use existing function if available)
function showAlert(message, type) {
  // Check if the showAlert function already exists in the global scope
  if (window.showAlert && typeof window.showAlert === "function") {
    window.showAlert(message, type);
    return;
  }

  // If not, create our own implementation
  const alertContainer = document.getElementById("shopAlert");
  if (!alertContainer) {
    // Create alert container if it doesn't exist
    const newAlertContainer = document.createElement("div");
    newAlertContainer.id = "shopAlert";
    newAlertContainer.className =
      "hidden fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 rounded-lg shadow-lg";
    document.body.appendChild(newAlertContainer);
  }

  const alertDiv = document.getElementById("shopAlert");
  alertDiv.textContent = message;
  alertDiv.className =
    type === "success"
      ? "fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 rounded-lg shadow-lg bg-green-600 text-white"
      : "fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 rounded-lg shadow-lg bg-red-600 text-white";

  // Hide alert after 3 seconds
  setTimeout(() => {
    alertDiv.className =
      "hidden fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 rounded-lg shadow-lg";
  }, 3000);
}
