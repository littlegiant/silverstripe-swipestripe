<!DOCTYPE html>
<html>
<head>
    <% base_tag %>
    <title>{$SiteConfig.Title} - Order #{$ID}</title>
    <meta name="viewport" id="viewport"
          content="width=device-width,minimum-scale=1.0,maximum-scale=10.0,initial-scale=1.0"/>
    <% require themedCss('dist/css/main.css') %>
</head>
<body>
    <header>
        <div class="container site-header">
            <div class="site-header-brand">
                <a title="<%t CWP_Header.Title "Go to Home Page" %>" class="site-header-brand-link-default"
                   href="$BaseHref">
                    <% if $SiteConfig.Logo %>
                        <img src="$SiteConfig.Logo.URL" width="$SiteConfig.Logo.Width" height="$SiteConfig.Logo.Height"
                             alt="$SiteConfig.Title" aria-hidden="true"/>
                    <% else %>
                        <span>$SiteConfig.Title</span>
                    <% end_if %>
                </a>

                <% if $SiteConfig.Tagline %>
                    <span class="site-header-brand-tagline small">{$SiteConfig.Tagline}</span>
                <% end_if %>
            </div>
        </div>
    </header>

    <main>{$Layout}</main>

    <footer class="footer-site">
        <div class="container">
            <p>&copy; {$CurrentDatetime.Format(Y)} {$SiteConfig.Title}</p>
            <p>View online: <a href="{$AbsoluteLink}">{$AbsoluteLink}</a></p>
        </div>
    </footer>
</body>
</html>
