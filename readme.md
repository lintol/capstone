<h1>Lintol</h1>

<p>Originally built in R&amp;D for the Open Data Institute (ODI), and further extended by the Lintol team.</p>

<h2>CKAN</h2>

<p>We generally recommend using CKAN as a data backend. By default, Lintol minimizes retention of information from CKAN, but can incorporate CKAN to search. Datasets that are pulled from CKAN and run through the Lintol system will, naturally, have greater information retention.</p>

<p>To link Lintol with a CKAN system for login and data sharing, a CKAN OAuth2 provider should be added for the Lintol deployment, and the CKAN URL (including scheme+/port but without trailing slash) should be included in the semicolon-separated `LINTOL_CKAN_SERVERS` environment variable. The CKAN OAuth2 provider should have the Lintol deployment's URL (without scheme or trailing slash) and redirect to `$LINTOL_URL/login/ckan/callback`.</p>

<p>Finally, you will need to switch on the feature flag, `LINTOL_FEATURE_REMOTE_DATA_RESOURCES`, if you intend to pull data directly from this server.</p>
