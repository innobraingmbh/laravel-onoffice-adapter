# Local onOffice API Docs

## Overview

The onOffice API docs are mirrored locally for offline reference and fast search.

- HTML mirror: `onoffice-docs/apidoc-html` (generated from `https://apidoc.onoffice.de/`)
- Text export: `onoffice-docs/apidoc-text` (searchable with `rg`)

Both folders are listed in `.gitignore` and are not committed.

## Regenerate HTML Mirror

```
wget --mirror --convert-links --adjust-extension --page-requisites --no-parent -P "onoffice-docs/apidoc-html" "https://apidoc.onoffice.de/"
```

## Regenerate Text Export

```
python - <<'PY'
import html
import os
import re
from html.parser import HTMLParser

source_root = "onoffice-docs/apidoc-html/apidoc.onoffice.de"
output_root = "onoffice-docs/apidoc-text"

block_tags = {
    "p", "br", "li", "ul", "ol", "h1", "h2", "h3", "h4", "h5", "h6",
    "pre", "code", "table", "tr", "td", "th", "section", "div", "header",
    "footer", "article", "nav"
}

class TextExtractor(HTMLParser):
    def __init__(self):
        super().__init__()
        self.chunks = []
        self.skip_depth = 0

    def handle_starttag(self, tag, attrs):
        if tag in {"script", "style", "noscript"}:
            self.skip_depth += 1
            return
        if tag in block_tags:
            self.chunks.append("\n")

    def handle_endtag(self, tag):
        if tag in {"script", "style", "noscript"}:
            self.skip_depth = max(0, self.skip_depth - 1)
            return
        if tag in block_tags:
            self.chunks.append("\n")

    def handle_data(self, data):
        if self.skip_depth:
            return
        text = data.strip()
        if text:
            self.chunks.append(text)
            self.chunks.append(" ")

def extract_text(contents: str) -> str:
    parser = TextExtractor()
    parser.feed(contents)
    raw = "".join(parser.chunks)
    raw = html.unescape(raw)
    raw = raw.replace("\r", "\n")
    raw = re.sub(r"[ \t]+", " ", raw)
    raw = re.sub(r"\n\s*\n+", "\n\n", raw)
    return raw.strip()

for root, _, files in os.walk(source_root):
    for file_name in files:
        if not file_name.endswith(".html"):
            continue
        source_path = os.path.join(root, file_name)
        rel_path = os.path.relpath(source_path, source_root)
        target_path = os.path.join(output_root, os.path.splitext(rel_path)[0] + ".txt")
        os.makedirs(os.path.dirname(target_path), exist_ok=True)
        try:
            with open(source_path, "r", encoding="utf-8", errors="ignore") as file:
                contents = file.read()
            text = extract_text(contents)
            with open(target_path, "w", encoding="utf-8") as file:
                file.write(text)
        except Exception:
            continue

print("done")
PY
```

## Search

```
rg "search term" onoffice-docs/apidoc-text
```

## Module Index (from apidoc mirror)

Source: `onoffice-docs/apidoc-text/index.txt`

**Covered**

- Estates
- Addresses
- Search Criteria
- Agents log / Activities
- Files and Templates (partial)
- Marketplace (`onoffice-docs/apidoc-text/marketplace/index.txt`)
- Settings (partial)

**Missing**

- Appointments (`onoffice-docs/apidoc-text/api-calls-sorted-by-module/appointments/index.txt`)
- Tasks (`onoffice-docs/apidoc-text/api-calls-sorted-by-module/tasks/index.txt`)
- Relations (`onoffice-docs/apidoc-text/api-calls-sorted-by-module/relations/index.txt`)
- Emails (`onoffice-docs/apidoc-text/api-calls-sorted-by-module/emails/index.txt`)
- Miscellaneous (`onoffice-docs/apidoc-text/api-calls-sorted-by-module/miscellaneous/index.txt`) (macros, logs, surveys, timetracking, links)
