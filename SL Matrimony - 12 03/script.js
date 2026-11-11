document.addEventListener("DOMContentLoaded", () => {
    const showMessage = (title, text, icon = "error") => {
        if (window.Swal) {
            return Swal.fire(title, text, icon);
        }
        window.alert(`${title}\n${text}`);
        return Promise.resolve();
    };

    const loginModal = document.getElementById("loginModal");
    const resetPasswordModal = document.getElementById("resetPasswordModal");
    const loginButton = document.getElementById("loginBtn");
    const logoutButton = document.getElementById("logoutBtn");

    const setModalVisible = (modal, visible) => {
        if (modal) {
            modal.style.display = visible ? "block" : "none";
        }
    };

    const updateAuthButtons = (loggedIn) => {
        if (loginButton) {
            loginButton.style.display = loggedIn ? "none" : "inline-block";
        }
        if (logoutButton) {
            logoutButton.style.display = loggedIn ? "inline-block" : "none";
        }
    };

    window.addEventListener("load", () => {
        const preloader = document.getElementById("preloader");
        if (preloader) {
            preloader.style.display = "none";
        }
    });

    fetch("session_check.php")
        .then((response) => {
            if (!response.ok) {
                throw new Error("Could not check the current login session.");
            }
            return response.json();
        })
        .then((session) => {
            updateAuthButtons(Boolean(session.loggedIn));
        })
        .catch((error) => {
            updateAuthButtons(false);
            console.error(error);
        });

    loginButton?.addEventListener("click", (event) => {
        event.preventDefault();
        setModalVisible(loginModal, true);
    });
    document.getElementById("closeLogin")?.addEventListener("click", () => {
        setModalVisible(loginModal, false);
    });

    logoutButton?.addEventListener("click", async (event) => {
        event.preventDefault();
        try {
            const response = await fetch("logout.php", { method: "POST" });
            if (!response.ok) {
                throw new Error("Unable to log out. Please try again.");
            }
            window.location.href = "index.html";
        } catch (error) {
            showMessage("Logout failed", error.message);
        }
    });

    document.getElementById("loginForm")?.addEventListener("submit", async (event) => {
        event.preventDefault();
        try {
            const response = await fetch("login.php", {
                method: "POST",
                body: new FormData(event.currentTarget)
            });
            const result = (await response.text()).trim();
            if (!response.ok || result !== "redirect_profiles") {
                throw new Error(result.replace(/^error:\s*/i, "") || "Login failed. Please try again.");
            }
            window.location.href = "profile.html";
        } catch (error) {
            showMessage("Login failed", error.message);
        }
    });

    document.getElementById("forgotPasswordLink")?.addEventListener("click", (event) => {
        event.preventDefault();
        setModalVisible(loginModal, false);
        setModalVisible(resetPasswordModal, true);
    });
    document.getElementById("closeResetPassword")?.addEventListener("click", () => {
        setModalVisible(resetPasswordModal, false);
    });

    document.getElementById("resetPasswordForm")?.addEventListener("submit", async (event) => {
        event.preventDefault();
        try {
            const response = await fetch("resetPassword.php", {
                method: "POST",
                body: new FormData(event.currentTarget)
            });
            const result = (await response.text()).trim();
            if (!response.ok || result !== "success") {
                throw new Error(result.replace(/^error:\s*/i, "") || "Could not reset the password.");
            }
            await showMessage("Password reset", "You can log in with your new password.", "success");
            setModalVisible(resetPasswordModal, false);
            setModalVisible(loginModal, true);
        } catch (error) {
            showMessage("Password reset failed", error.message);
        }
    });

    document.getElementById("registrationForm")?.addEventListener("submit", async (event) => {
        event.preventDefault();
        try {
            const response = await fetch("customer_register.php", {
                method: "POST",
                body: new FormData(event.currentTarget)
            });
            const result = (await response.text()).trim();
            if (!response.ok || result !== "success") {
                throw new Error(result.replace(/^error:\s*/i, "") || "Registration failed. Please try again.");
            }
            await showMessage("Registration successful", "Your profile is saved. Please log in to continue.", "success");
            setModalVisible(loginModal, true);
        } catch (error) {
            showMessage("Registration failed", error.message);
        }
    });

    document.querySelectorAll(".modal").forEach((modal) => {
        modal.addEventListener("click", (event) => {
            if (event.target === modal) {
                setModalVisible(modal, false);
            }
        });
    });

    const profileGrid = document.getElementById("profileGrid");
    const profileStatus = document.getElementById("profileStatus");
    if (profileGrid && profileStatus) {
        profileGrid.replaceChildren();
        fetch("get_profiles.php")
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.error || "Could not load member profiles.");
                }
                return data.profiles || [];
            })
            .then((profiles) => {
                profileStatus.textContent = profiles.length ? `${profiles.length} profiles found.` : "No matching profiles are available yet.";
                profiles.forEach((profile) => {
                    const column = document.createElement("div");
                    column.className = "col-lg-4 col-md-6";
                    const card = document.createElement("article");
                    card.className = "profile-card h-100";
                    const image = document.createElement("img");
                    image.className = "profile-img";
                    image.src = profile.photo_url || "default.jpg";
                    image.alt = `${profile.name || "Member"} profile photo`;
                    const info = document.createElement("div");
                    info.className = "profile-info";
                    const name = document.createElement("h5");
                    name.textContent = profile.name || "Member";
                    const details = document.createElement("p");
                    details.textContent = [profile.employment_status, profile.current_city].filter(Boolean).join(" · ") || "Profile available";
                    const link = document.createElement("a");
                    link.className = "btn btn-primary btn-sm";
                    link.href = `view_profile.php?id=${encodeURIComponent(profile.id)}`;
                    link.textContent = "View Profile";
                    info.append(name, details, link);
                    card.append(image, info);
                    card.dataset.search = [profile.name, profile.current_city, profile.higher_education, profile.religion, profile.employment_status]
                        .filter(Boolean).join(" ").toLowerCase();
                    card.dataset.city = (profile.current_city || "").toLowerCase();
                    column.append(card);
                    profileGrid.append(column);
                });
                applyProfileFilters();
            })
            .catch((error) => {
                profileStatus.textContent = error.message;
                const signInLink = document.createElement("a");
                signInLink.href = "#";
                signInLink.textContent = " Log in to view profiles.";
                signInLink.addEventListener("click", (event) => {
                    event.preventDefault();
                    setModalVisible(loginModal, true);
                });
                profileStatus.append(signInLink);
            });

        const searchInput = document.getElementById("profileSearch");
        const cityInput = document.getElementById("cityFilter");
        function applyProfileFilters() {
            const search = (searchInput?.value || "").trim().toLowerCase();
            const city = (cityInput?.value || "").trim().toLowerCase();
            let visible = 0;
            profileGrid.querySelectorAll(".profile-card").forEach((card) => {
                const matches = card.dataset.search.includes(search) && card.dataset.city.includes(city);
                card.closest(".col-lg-4").hidden = !matches;
                if (matches) {
                    visible += 1;
                }
            });
            if (search || city) {
                profileStatus.textContent = `${visible} matching profile${visible === 1 ? "" : "s"}.`;
            }
        }
        searchInput?.addEventListener("input", applyProfileFilters);
        cityInput?.addEventListener("input", applyProfileFilters);
    }

    document.querySelectorAll(".subscribe-package").forEach((button) => {
        button.addEventListener("click", async () => {
            button.disabled = true;
            try {
                const body = new FormData();
                body.append("package", button.dataset.package || "");
                const response = await fetch("subscribe.php", { method: "POST", body });
                const result = (await response.text()).trim();
                if (!response.ok || !result.startsWith("success:")) {
                    throw new Error(result.replace(/^error:\s*/i, "") || "Could not save your package request.");
                }
                await showMessage("Package request saved", result.slice("success:".length).trim(), "success");
            } catch (error) {
                await showMessage("Package request failed", error.message);
                if (error.message.toLowerCase().includes("log in")) {
                    setModalVisible(loginModal, true);
                }
            } finally {
                button.disabled = false;
            }
        });
    });
});

function loadCompatibility(zodiac, nakshathira, callback) {
    fetch("compatability.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ zodiac, nakshathira })
    })
        .then((response) => response.text())
        .then(callback)
        .catch((error) => {
            console.error("Could not load compatibility:", error);
            callback("Unable to load compatibility. Please try again.");
        });
}
