/**
 * Rollbar for WordPress admin JS.
 *
 * @var {{
 *    nonce: string,
 *    rest_nonce: string,
 *    rest_root: string,
 *    plugin_url: string,
 * }} rollbarSettings Declared in SettingsPage::enqueueAdminScripts().
 */

// import Rollbar from 'rollbar';

document.addEventListener('DOMContentLoaded', () => {
    // Collapse Settings Sections Toggles
    const sectionHeaders = document.querySelectorAll('.rollbar-settings-section-header');
    sectionHeaders.forEach(header => {
        const toggle = header.querySelector('.section-heading');
        toggle.addEventListener('click', () => {
            header.classList.toggle('open');
        });
    });

    const enablePhpInput = document.getElementById('rollbar_wp_php_logging_enabled');
    const enableJsInput = document.getElementById('rollbar_wp_js_logging_enabled');

    // Toggle PHP/JS logging
    Object.entries({
        "rollbar_wp_server_side_access_token_container": enablePhpInput,
        "rollbar_wp_client_side_access_token_container": enableJsInput,
    }).forEach(([container, el]) => {
        // Include here so they start open.
        if (el.checked) {
            document.getElementById(container).classList.remove('hidden');
        }
        el.addEventListener('input', () => {
            if (el.checked) {
                document.getElementById(container).classList.remove('hidden');
                return;
            }
            document.getElementById(container).classList.add('hidden');
        });
    });

    // Reset Default
    document.querySelectorAll('.rollbar_wp_restore_default').forEach(el => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            const setting = el.getAttribute('data-setting');
            let defaultValue = el.getAttribute('data-default');
            const type = el.getAttribute('data-setting-input-type');
            const input = document.querySelectorAll(`[data-setting="${setting}"]`);
            if (null === input || input.length === 0) {
                return;
            }
            switch (type) {
                case 'boolean':
                    input[0].checked = defaultValue === 'true';
                    break;
                case 'checkbox':
                    defaultValue = defaultValue.split(',');
                    for (const checkbox of input) {
                        checkbox.checked = defaultValue.includes(checkbox.value);
                    }
                    break;
                default:
                    input[0].value = defaultValue;
            }
        })
    });

    const testMessageContainer = document.getElementById('rollbar_test_message_container');

    /**
     * Escapes HTML characters in the string.
     *
     * @param {string} str The string to escape.
     * @returns {string}
     */
    const escapeHtml = (str) => {
        return new Option(str).innerHTML;
    };

    /**
     * Escapes HTML characters in the string for use in an attribute.
     *
     * @param {string} str The string to escape.
     * @returns {string}
     */
    const escapeHtmlAttr = (str) => {
        return ('' + str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    };

    /**
     * Returns a formatted notice message HTML Node.
     *
     * @param {string} type    The notice type class.
     * @param {string} message The notice message. The message should be escaped before being passed in.
     * @returns {ChildNode}
     */
    const makeNotice = (type, message) => {
        const el = document.createElement('div');
        el.innerHTML = `<div class="notice ${escapeHtmlAttr(type)} is-dismissible">${message}</div>`;
        return el.firstChild;
    };

    /**
     * Collects the Rollbar settings from the form.
     *
     * @returns {object} The Rollbar settings.
     */
    const collectSettings = () => {
        const settings = {};
        document.querySelectorAll('.wrap form [name^="rollbar_wp"]').forEach(input => {
            const isArray = input.name.endsWith('[]');
            let name = input.name.slice(input.name.indexOf('[') + 1, input.name.indexOf(']'));
            let value = input.value;
            if (input.type === 'checkbox' && !isArray) {
                value = input.checked;
            }
            if (input.type === 'number') {
                value = parseInt(value);
            }
            if (isArray) {
                if (!(name in settings)) {
                    settings[name] = [];
                }
                if (input.type === 'checkbox' && !input.checked) {
                    return;
                }
                settings[name].push(value);
                return;
            }
            settings[name] = value;
        });
        return settings;
    };

    /**
     * Request a test of the Rollbar config in PHP via the REST API.
     */
    const testPhpLogging = () => {
        if (enablePhpInput.checked === false) {
            testMessageContainer.appendChild(makeNotice(
                'notice-warning',
                `<p><strong>PHP Test:</strong> Skipped testing logging since it is disabled.</p>`,
            ));
            return;
        }
        fetch(`${rollbarSettings.rest_root}rollbar/v1/test-php-logging`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': rollbarSettings.rest_nonce,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(collectSettings()),
        }).then(response => {
            response.json().then(data => {
                if (data.success) {
                    testMessageContainer.appendChild(makeNotice(
                        'notice-success',
                        `<p><strong>PHP Test:</strong> Test message sent to Rollbar using PHP. Please check 
                                      your Rollbar dashboard to see if you received it. Save your changes and you're 
                                      ready to go.</p>`,
                    ));
                    return;
                }
                testMessageContainer.appendChild(makeNotice(
                    'notice-error',
                    `<p><strong>PHP Test:</strong> There was a problem accessing Rollbar service.</p>
                              <ul>
                                  <li>Code:<code>${escapeHtml(data.code)}</code></li>
                                  <li>Message:<code>${escapeHtml(data.message)}</code></li>
                              </ul>`,
                ));
            })
        }).finally(() => {
            testButton.disabled = false;
        });
    };

    /**
     * Send the test log message to the Rollbar service.
     *
     * @param {string} token The client side access token.
     * @param {string} env   The environment.
     */
    const sendTestJsMessage = (token, env) => {
        Rollbar.configure({
            accessToken: token,
            captureUncaught: true,
            captureUnhandledRejections: true,
            payload: {
                environment: env
            }
        });
        Rollbar.info(
            "Test message from Rollbar WordPress plugin using JS: integration with WordPress successful",
            function(error, data) {
                if (undefined !== data) {
                    testMessageContainer.appendChild(makeNotice(
                        'notice-success',
                        `<p><strong>JS Test:</strong> Test message sent to Rollbar using JS. Please check 
                                      your Rollbar dashboard to see if you received it. Save your changes and you're 
                                      ready to go.</p>`,
                    ));
                    return;
                }
                testMessageContainer.appendChild(makeNotice(
                    'notice-error',
                    `<p><strong>JS Test:</strong> There was a problem accessing Rollbar service using provided credentials for JS logging. Check your client side token.</p>`,
                ));
            }
        );
    };

    /**
     * Test the Rollbar JS configuration to ensure it is working.
     */
    const testJsLogging = () => {
        const clientSideAccessToken = document.getElementById('rollbar_wp_client_side_access_token').value;
        const environment = document.getElementById('rollbar_wp_environment').value;
        if (enableJsInput.checked === false) {
            testMessageContainer.appendChild(makeNotice(
                'notice-warning',
                `<p><strong>JS Test:</strong> Skipped testing logging since it is disabled.</p>`,
            ));
            return;
        }

        // Depending on the plugin status prior to loading the page, the Rollbar object may not exist. So we fetch it.
        if (window.Rollbar !== undefined) {
            sendTestJsMessage(clientSideAccessToken, environment);
            return;
        }
        // If the Rollbar object doesn't exist, we need to load and configure it.
        window._rollbarConfig = {
            accessToken: clientSideAccessToken,
            captureUncaught: true,
            captureUnhandledRejections: true,
            payload: {
                environment: environment
            }
        }
        // Load the Rollbar JS library.
        const script = document.createElement('script');
        script.src = rollbarSettings.plugin_url + "public/js/rollbar.snippet.js";
        let mainRollbarScript = document.querySelector('script[src$="rollbar.min.js"]');

        // Wait for both Rollbar JS libraries to load before sending the test message.
        script.addEventListener('load', () => {
            if (window.Rollbar !== undefined) {
                sendTestJsMessage(clientSideAccessToken, environment);
                return;
            }
            document.querySelector('script[src$="rollbar.min.js"]')?.addEventListener('load', () => {
                sendTestJsMessage(clientSideAccessToken, environment);
            });
        });

        // Handle error loading either the snippet or main Rollbar JS library.
        window.addEventListener('error', (e) => {
            if (e.target === script || e.target === mainRollbarScript) {
                testMessageContainer.appendChild(makeNotice(
                    'notice-error',
                    `<p><strong>JS Test:</strong> There was an error loading the Rollbar JS library.</p>`,
                ));
            }
        }, true);

        document.body.appendChild(script);
    };

    // Send Test Message
    const testButton = document.getElementById('rollbar_wp_test_logging');
    testButton?.addEventListener('click', () => {
        testButton.disabled = true;
        testMessageContainer.innerHTML = '';
        testPhpLogging();
        testJsLogging();
    });
});
