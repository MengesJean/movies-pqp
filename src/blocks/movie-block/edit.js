/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from "@wordpress/block-editor";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import "./editor.scss";

import { RadioControl, SelectControl } from "@wordpress/components";
import { useEffect, useState } from "@wordpress/element";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
  console.log(attributes);
  const { displayType, movieId } = attributes;
  const [movies, setMovies] = useState([]);
  // block unique id
  const block = useBlockProps();
  const blockId = block.id;
  useEffect(() => {
    fetchMovies().then((movies) => {
      setMovies(movies);
      if (movies.length > 0 && !movieId) {
        setAttributes({ movieId: movies[0].id });
      }
    });
  }, []);
  return (
    <div {...useBlockProps()}>
      <div>
        <div>
          <RadioControl
            label="Display Type"
            selected={displayType}
            options={[
              { label: "Single", value: "single" },
              { label: "Multiple", value: "multiple" },
            ]}
            onChange={(value) => setAttributes({ displayType: value })}
          />
        </div>
      </div>
      {displayType === "single" && (
        <MovieSelector
          movies={movies}
          movieId={movieId}
          handleChange={setAttributes}
        />
      )}
    </div>
  );
}

const MovieSelector = ({ movies, movieId, handleChange }) => {
  if (movies.length === 0) return null;
  return (
    <div>
      <SelectControl
        label="Movie"
        defaultValue={movieId}
        value={movieId}
        options={movies.map((movie) => ({
          label: movie.title.rendered,
          value: movie.id,
        }))}
        onChange={(value) => handleChange({ movieId: parseInt(value) })}
      />
    </div>
  );
};

const fetchMovies = async () => {
  const response = await fetch("/wp-json/wp/v2/movie?&_embed");
  return response.json();
};
