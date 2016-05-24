import React, { PropTypes, Component } from 'react';
import config from 'config';
import Widget from '../../Widget';
import MeasuresWidget from './MeasuresWidget';
import MeasuresPage from './MeasuresPage';
import MeasureEditPage from './MeasureEditPage';
import { Link } from 'react-router';

class Measures extends Component {
  
  constructor(props) {
    super(props);
    Widget.registerWidget(this, props);
  }

  componentWillMount () {
    Widget.getPayload(this, config.apiUrl + 'measures', this.processData);
  }

  processData (data) {
    // return data;
    return {
      measures: []
    }
  }

  emptyText(includeLink) {
    return (
      <div className="alert alert-warning">
        <span>No measures added. Please </span>
        {includeLink && (
          <Link to='/dashboard/Measures/new'>add some!</Link>
        )}
        {!includeLink && (
          <span>add some!</span>
        )}
      </div>
    );
  }

  handleSubmit(data) {
    console.log(data);
  }

  measureDelete(measure) {
    console.log(measure);
  }

  render () {

    let widget = this.props.widget;
    
    // Return loading if not set
    if(!widget || widget.status === 'loading') {
      return Widget.loadingDisplay();
    }
    else if(widget.status === 'load_failed') {
      // return Widget.loadFailed(widget.widgetName, true);
      return (
        <div className="panel panel-default"><div className="panel-body">
          <p>Sorry no measures at the moment</p>
        </div></div>
      )
    }

    if(this.props.display === 'pageIndividual') {
      let measure, headerText;

      // Creating new measure
      if(this.props.isNew){
        measure = {
          '_id': '',
          'title': '',
          'description': '',
          'frequency': '',
          'startdate': '',
          'confirmDelete': ''
        };
      }
      // not a new measure, so filter
      else if(this.props.individual) {
        widget.data.measures.filter((item) => {
          if(this.props.individual === item._id) {
            measure = item
          }
        });
      }

      if(!measure) {
        return (
          <div>
            <h2>Sorry there was an issue editing the measure.</h2>
            {Widget.backLink('Go back', 'btn btn-default')}
          </div>
        )
      }
      
      return (
        <MeasureEditPage 
          header={Widget.titleSection(headerText, false, 'h2', false, true)} 
          measureData={measure}
          measureSubmit={this.handleSubmit.bind(this)}
          measureDelete={this.measureDelete.bind(this)}
          emptyText={this.emptyText()}
          backLink={Widget.backLink('Cancel', 'btn btn-default')} />
      )
    }
    else if(this.props.display === 'page') {
      return (
        <MeasuresPage
          header={Widget.titleSection('Measures', false, 'h2', false, true)} 
          emptyText={this.emptyText(true)} 
          measures={widget.data.measures} />
      )
    }
    else {
      let lastRun = 'Never';
      let totalMeasures = 0;
      // Compile data
      if (widget.data && widget.status === 'loaded') {
        if(widget.data.measures && widget.data.measures.length) {
          lastRun = widget.data.last_checked;
          totalMeasures = widget.data.measures.length;
        }
      }
      return (
        <MeasuresWidget 
          lastRun={lastRun} 
          totalMeasures={totalMeasures} 
          footer={Widget.panelFooter(totalMeasures + ' total measures', this.props.widgetName)} />
      )
    }
  }
}

Measures.propTypes = Widget.propTypes({
  individual: PropTypes.number,
  isNew: PropTypes.boolean
});
Measures.defaultProps = Widget.defaultProps();

export default Widget.connect(Measures);