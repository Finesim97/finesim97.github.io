require 'jekyll/scholar'
require 'uri'

module HTMLFilter
  class HTML < BibTeX::Filter
    def apply(value)
      value.to_s.gsub(URI.regexp(['http','https','ftp'])) { |c| "<a href=\"#{$&}\">Link</a>" }
    end
  end
end