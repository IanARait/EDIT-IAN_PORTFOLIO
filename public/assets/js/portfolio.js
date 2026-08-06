/**
 * Portfolio Page JavaScript
 * ES6+ Vanilla JS - No jQuery
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const projectGrid = document.querySelector('.portfolio-grid');
  const filterButtons = document.querySelectorAll('.filter-btn, [data-filter]');
  const searchInput = document.querySelector('.portfolio-search, #portfolio-search');
  const loadMoreBtn = document.querySelector('.load-more-btn');
  const sortSelect = document.querySelector('.portfolio-sort, #portfolio-sort');
  const noResults = document.querySelector('.no-results');
  const projectCards = () => document.querySelectorAll('.portfolio-card');

  let currentFilter = 'all';
  let currentSearch = '';
  let currentSort = 'default';
  let visibleCount = 6;
  const increment = 6;

  // ─── Filter Tabs ───────────────────────────────────────────
  const setActiveFilter = (filter) => {
    currentFilter = filter;
    filterButtons.forEach((btn) => {
      const btnFilter = btn.getAttribute('data-filter') || btn.textContent.trim().toLowerCase();
      btn.classList.toggle('active', btnFilter === filter);
    });
  };

  const filterProjects = () => {
    const cards = projectCards();
    let visibleCards = 0;

    cards.forEach((card, index) => {
      const category = (card.getAttribute('data-category') || '').toLowerCase();
      const title = (card.getAttribute('data-title') || card.querySelector('.card-title, h3, h4')?.textContent || '').toLowerCase();
      const client = (card.getAttribute('data-client') || '').toLowerCase();

      const matchesFilter = currentFilter === 'all' || category.includes(currentFilter);
      const matchesSearch =
        currentSearch === '' ||
        title.includes(currentSearch) ||
        client.includes(currentSearch);

      if (matchesFilter && matchesSearch) {
        visibleCards++;
        if (visibleCards <= visibleCount) {
          card.style.display = '';
          card.classList.add('visible');

          if (typeof gsap !== 'undefined') {
            gsap.fromTo(
              card,
              { opacity: 0, y: 30, scale: 0.95 },
              {
                opacity: 1,
                y: 0,
                scale: 1,
                duration: 0.4,
                delay: visibleCards * 0.05,
                ease: 'power2.out',
              }
            );
          } else {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            requestAnimationFrame(() => {
              card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
              card.style.opacity = '1';
              card.style.transform = 'translateY(0)';
            });
          }
        } else {
          card.style.display = 'none';
          card.classList.remove('visible');
        }
      } else {
        card.style.display = 'none';
        card.classList.remove('visible');
      }
    });

    // Load more button visibility
    if (loadMoreBtn) {
      const totalMatching = [...cards].filter((card) => {
        const category = (card.getAttribute('data-category') || '').toLowerCase();
        const title = (card.getAttribute('data-title') || card.querySelector('.card-title, h3, h4')?.textContent || '').toLowerCase();
        const client = (card.getAttribute('data-client') || '').toLowerCase();
        const matchesFilter = currentFilter === 'all' || category.includes(currentFilter);
        const matchesSearch = currentSearch === '' || title.includes(currentSearch) || client.includes(currentSearch);
        return matchesFilter && matchesSearch;
      }).length;

      loadMoreBtn.style.display = visibleCards >= totalMatching ? 'none' : '';
    }

    // No results
    if (noResults) {
      noResults.style.display = visibleCards === 0 ? 'flex' : 'none';
    }

    // Re-layout masonry
    adjustMasonry();
  };

  filterButtons.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const filter = btn.getAttribute('data-filter') || btn.textContent.trim().toLowerCase();
      setActiveFilter(filter);
      filterProjects();
    });
  });

  // ─── Portfolio Search with Debounce ────────────────────────
  const debounce = (func, wait) => {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => func(...args), wait);
    };
  };

  const handleSearch = debounce((value) => {
    currentSearch = value.toLowerCase().trim();
    visibleCount = 6;
    filterProjects();
  }, 300);

  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      handleSearch(e.target.value);
    });

    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        searchInput.value = '';
        currentSearch = '';
        visibleCount = 6;
        filterProjects();
      }
    });
  }

  // ─── Sort Projects ────────────────────────────────────────
  const sortProjects = (criteria) => {
    currentSort = criteria;
    if (!projectGrid) return;

    const cards = [...projectCards()];
    if (cards.length === 0) return;

    cards.sort((a, b) => {
      const titleA = (a.getAttribute('data-title') || a.querySelector('.card-title, h3, h4')?.textContent || '').toLowerCase();
      const titleB = (b.getAttribute('data-title') || b.querySelector('.card-title, h3, h4')?.textContent || '').toLowerCase();
      const dateA = a.getAttribute('data-date') || '';
      const dateB = b.getAttribute('data-date') || '';

      switch (criteria) {
        case 'title-asc':
          return titleA.localeCompare(titleB);
        case 'title-desc':
          return titleB.localeCompare(titleA);
        case 'date-newest':
          return dateB.localeCompare(dateA);
        case 'date-oldest':
          return dateA.localeCompare(dateB);
        default:
          return 0;
      }
    });

    cards.forEach((card) => projectGrid.appendChild(card));

    if (typeof gsap !== 'undefined') {
      gsap.from(cards, {
        opacity: 0,
        y: 20,
        duration: 0.4,
        stagger: 0.05,
        ease: 'power2.out',
      });
    }
  };

  if (sortSelect) {
    sortSelect.addEventListener('change', (e) => {
      sortProjects(e.target.value);
    });
  }

  // ─── Load More ────────────────────────────────────────────
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', () => {
      visibleCount += increment;
      filterProjects();
    });
  }

  // ─── Project Modal ────────────────────────────────────────
  const modal = document.querySelector('.project-modal, #projectModal');
  const modalContent = modal ? modal.querySelector('.modal-body, .modal-content') : null;

  const populateModal = (card) => {
    if (!modalContent || !modal) return;

    const data = {
      title: card.getAttribute('data-title') || '',
      description: card.getAttribute('data-description') || card.querySelector('.card-description, p')?.textContent || '',
      client: card.getAttribute('data-client') || '',
      software: card.getAttribute('data-software') || '',
      duration: card.getAttribute('data-duration') || '',
      category: card.getAttribute('data-category') || '',
      videoUrl: card.getAttribute('data-video') || '',
      image: card.getAttribute('data-image') || card.querySelector('img')?.src || '',
    };

    const videoContainer = modalContent.querySelector('.modal-video');
    const modalTitle = modalContent.querySelector('.modal-title, [data-modal-title]');
    const modalDesc = modalContent.querySelector('.modal-description, [data-modal-description]');
    const modalClient = modalContent.querySelector('.modal-client, [data-modal-client]');
    const modalSoftware = modalContent.querySelector('.modal-software, [data-modal-software]');
    const modalDuration = modalContent.querySelector('.modal-duration, [data-modal-duration]');
    const modalCategory = modalContent.querySelector('.modal-category, [data-modal-category]');

    if (videoContainer) {
      videoContainer.innerHTML = '';
      if (data.videoUrl) {
        const embedUrl = getEmbedUrl(data.videoUrl);
        if (embedUrl) {
          videoContainer.innerHTML = `<iframe src="${embedUrl}" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" style="width:100%;aspect-ratio:16/9;border-radius:8px;"></iframe>`;
        } else {
          videoContainer.innerHTML = `<img src="${data.image}" alt="${data.title}" style="width:100%;border-radius:8px;">`;
        }
      } else if (data.image) {
        videoContainer.innerHTML = `<img src="${data.image}" alt="${data.title}" style="width:100%;border-radius:8px;">`;
      }
    }

    if (modalTitle) modalTitle.textContent = data.title;
    if (modalDesc) modalDesc.textContent = data.description;
    if (modalClient) modalClient.textContent = data.client;
    if (modalSoftware) modalSoftware.textContent = data.software;
    if (modalDuration) modalDuration.textContent = data.duration;
    if (modalCategory) modalCategory.textContent = data.category;

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  };

  const getEmbedUrl = (url) => {
    if (!url) return '';
    // YouTube
    const ytMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (ytMatch) return `https://www.youtube.com/embed/${ytMatch[1]}?autoplay=0`;
    // Vimeo
    const vimeoMatch = url.match(/(?:vimeo\.com\/)(\d+)/);
    if (vimeoMatch) return `https://player.vimeo.com/video/${vimeoMatch[1]}`;
    return '';
  };

  const closeProjectModal = () => {
    if (!modal) return;

    const iframe = modal.querySelector('iframe');
    if (iframe) {
      iframe.src = '';
    }
    const video = modal.querySelector('video');
    if (video) {
      video.pause();
      video.currentTime = 0;
    }

    modal.classList.remove('active');
    document.body.style.overflow = '';
  };

  // Delegate click to portfolio cards
  if (projectGrid) {
    projectGrid.addEventListener('click', (e) => {
      const card = e.target.closest('.portfolio-card, [data-project]');
      if (!card) return;

      const modalTrigger = e.target.closest('[data-modal-open], .card-overlay, .card-link, .view-project');
      if (modalTrigger || card.hasAttribute('data-project')) {
        e.preventDefault();
        populateModal(card);
      }
    });
  }

  // Modal close handlers
  if (modal) {
    modal.querySelectorAll('.modal-close, [data-modal-close]').forEach((btn) => {
      btn.addEventListener('click', closeProjectModal);
    });

    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeProjectModal();
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
      closeProjectModal();
    }
  });

  // ─── Masonry-like Grid Layout ─────────────────────────────
  const adjustMasonry = () => {
    if (!projectGrid) return;

    const style = getComputedStyle(projectGrid);
    const gridTemplate = style.gridTemplateColumns;
    if (!gridTemplate || gridTemplate === 'none') return;

    const columns = gridTemplate.split(' ').length;
    if (columns <= 1) return;

    const visibleCards = [...projectCards()].filter((card) => {
      return card.style.display !== 'none' && card.classList.contains('visible');
    });

    const columnHeights = new Array(columns).fill(0);

    visibleCards.forEach((card) => {
      const shortestCol = columnHeights.indexOf(Math.min(...columnHeights));
      card.style.gridColumn = `${shortestCol + 1}`;
      columnHeights[shortestCol] += card.offsetHeight + parseInt(style.gap || '0', 10);
    });
  };

  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(adjustMasonry, 150);
  });

  // ─── GSAP Stagger Animation on Filter Change ──────────────
  const animateFilterChange = () => {
    if (typeof gsap === 'undefined') return;
    const visible = [...projectCards()].filter((c) => c.style.display !== 'none');
    gsap.from(visible, {
      opacity: 0,
      y: 40,
      scale: 0.95,
      duration: 0.5,
      stagger: 0.08,
      ease: 'power3.out',
      clearProps: 'all',
    });
  };

  // Override filter to include animation
  const originalFilter = filterProjects;
  const enhancedFilter = () => {
    originalFilter();
    animateFilterChange();
  };

  // Patch filter buttons to use enhanced filter
  filterButtons.forEach((btn) => {
    btn.removeEventListener('click', btn._handler);
    btn._handler = (e) => {
      e.preventDefault();
      const filter = btn.getAttribute('data-filter') || btn.textContent.trim().toLowerCase();
      setActiveFilter(filter);
      enhancedFilter();
    };
    btn.addEventListener('click', btn._handler);
  });

  // ─── Keyboard Navigation ──────────────────────────────────
  document.addEventListener('keydown', (e) => {
    if (e.key === '/' && document.activeElement !== searchInput && searchInput) {
      e.preventDefault();
      searchInput.focus();
    }
  });

  // ─── Initialize ───────────────────────────────────────────
  filterProjects();
});
