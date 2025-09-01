// Check if user is logged in
async function checkAuth() {
  try {
    const response = await fetch("backend/api/users/check_auth.php");
    const data = await response.json();

    if (data.status) {
      // User is logged in
      return {
        isLoggedIn: true,
        user: data.data,
      };
    } else {
      // User is not logged in
      return {
        isLoggedIn: false,
        user: null,
      };
    }
  } catch (error) {
    console.error("Error checking authentication:", error);
    return {
      isLoggedIn: false,
      user: null,
    };
  }
}

// Logout user
async function logout() {
  try {
    const response = await fetch("backend/api/users/logout.php", {
      method: "POST",
    });

    const data = await response.json();

    if (data.status) {
      // Clear local storage
      localStorage.removeItem("user");

      // Redirect to home page
      window.location.href = "index.html";
    } else {
      console.error("Logout failed:", data.message);
    }
  } catch (error) {
    console.error("Error during logout:", error);
  }
}

// Update navigation based on authentication status
async function updateNavigation() {
  const { isLoggedIn, user } = await checkAuth();

  const navContainer = document.querySelector(
    ".md\\:flex.justify-between.items-center.gap-8"
  );

  if (!navContainer) return;

  // Remove existing auth buttons if they exist
  const existingAuthButtons = document.getElementById("auth-buttons");
  if (existingAuthButtons) {
    existingAuthButtons.remove();
  }

  // Create auth buttons container
  const authButtons = document.createElement("div");
  authButtons.id = "auth-buttons";
  authButtons.className = "flex items-center gap-4";

  if (isLoggedIn) {
    // User is logged in
    const username = document.createElement("span");
    username.className = "text-white";
    username.textContent = `Welcome, ${user.username}`;

    const profileLink = document.createElement("a");
    profileLink.href = "profile.html";
    profileLink.className =
      "text-white bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg transition duration-300";
    profileLink.textContent = "My Profile";

    const logoutButton = document.createElement("button");
    logoutButton.className =
      "text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition duration-300";
    logoutButton.textContent = "Logout";
    logoutButton.addEventListener("click", logout);

    authButtons.appendChild(username);
    authButtons.appendChild(profileLink);
    authButtons.appendChild(logoutButton);

    // No cart or wishlist links are added
  } else {
    // User is not logged in
    const loginLink = document.createElement("a");
    loginLink.href = "login.html";
    loginLink.className =
      "text-white bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg transition duration-300";
    loginLink.textContent = "Login";

    const signupLink = document.createElement("a");
    signupLink.href = "signup.html";
    signupLink.className =
      "text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition duration-300";
    signupLink.textContent = "Sign Up";

    authButtons.appendChild(loginLink);
    authButtons.appendChild(signupLink);
  }

  // Add auth buttons to navigation
  navContainer.appendChild(authButtons);

  // Update mobile menu
  updateMobileMenu(isLoggedIn, user);
}

// Update mobile menu based on authentication status
function updateMobileMenu(isLoggedIn, user) {
  const mobileMenu = document.getElementById("mobileMenu");

  if (!mobileMenu) return;

  // Remove existing auth buttons if they exist
  const existingMobileAuthButtons = document.getElementById(
    "mobile-auth-buttons"
  );
  if (existingMobileAuthButtons) {
    existingMobileAuthButtons.remove();
  }

  // Create mobile auth buttons container
  const mobileAuthButtons = document.createElement("div");
  mobileAuthButtons.id = "mobile-auth-buttons";
  mobileAuthButtons.className = "mt-4 space-y-2";

  if (isLoggedIn) {
    // User is logged in
    const username = document.createElement("div");
    username.className = "text-white text-center font-bold";
    username.textContent = `Welcome, ${user.username}`;

    const profileLink = document.createElement("a");
    profileLink.href = "profile.html";
    profileLink.className =
      "block py-2 px-3 rounded bg-gray-700 hover:bg-gray-600 text-white text-center transition-colors duration-300";
    profileLink.textContent = "My Profile";

    const logoutButton = document.createElement("button");
    logoutButton.className =
      "w-full py-2 px-3 rounded bg-red-600 hover:bg-red-700 text-white text-center transition-colors duration-300";
    logoutButton.textContent = "Logout";
    logoutButton.addEventListener("click", logout);

    mobileAuthButtons.appendChild(username);
    mobileAuthButtons.appendChild(profileLink);
    mobileAuthButtons.appendChild(logoutButton);
  } else {
    // User is not logged in
    const loginLink = document.createElement("a");
    loginLink.href = "login.html";
    loginLink.className =
      "block py-2 px-3 rounded bg-gray-700 hover:bg-gray-600 text-white text-center transition-colors duration-300";
    loginLink.textContent = "Login";

    const signupLink = document.createElement("a");
    signupLink.href = "signup.html";
    signupLink.className =
      "block py-2 px-3 rounded bg-red-600 hover:bg-red-700 text-white text-center transition-colors duration-300";
    signupLink.textContent = "Sign Up";

    mobileAuthButtons.appendChild(loginLink);
    mobileAuthButtons.appendChild(signupLink);
  }

  // Add mobile auth buttons to mobile menu
  mobileMenu.appendChild(mobileAuthButtons);
}

// Initialize auth on page load
document.addEventListener("DOMContentLoaded", updateNavigation);
