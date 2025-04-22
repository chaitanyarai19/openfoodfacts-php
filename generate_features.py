import os
import re

def extract_features_from_php(file_path):
    features = []
    with open(file_path, 'r', encoding='utf-8') as file:
        content = file.read()
        matches = re.findall(r"/\*\*(.*?)\*/\s*(public|private|protected)?\s*function\s+(\w+)\s*\((.*?)\)\s*{", content, re.DOTALL)
        for docblock, visibility, function_name, params in matches:
            docblock = re.sub(r"\s*\*\s?", "", docblock.strip())
            description = ""
            for line in docblock.split("\n"):
                if not line.startswith("@"):
                    description += line.strip() + " "
            description = description.strip()
            function_code = re.search(
                rf"(public|private|protected)?\s*function\s+{function_name}\s*\(.*?\)\s*{{.*?}}",
                content,
                re.DOTALL
            )
            if function_code:
                function_code = function_code.group(0).strip()
            features.append({
                "name": function_name,
                "description": description,
                "details": function_code
            })
    return features

def generate_features_md(output_path, root_dir):
    features = []
    for root, _, files in os.walk(root_dir):
        for file in files:
            if file.endswith(".php"):
                file_path = os.path.join(root, file)
                features.extend(extract_features_from_php(file_path))
    with open(output_path, 'w', encoding='utf-8') as output_file:
        output_file.write("# Features\n\n")
        for feature in features:
            output_file.write(f"## Function Name: {feature['name']}\n\n")
            output_file.write(f"**Description:** {feature['description']}\n\n")
            output_file.write(f"**Function Details:**\n\n")
            output_file.write(f"```php\n{feature['details']}\n```\n")
            output_file.write("\n<hr>\n\n")

if __name__ == "__main__":
    generate_features_md("doc/features.md", "src")